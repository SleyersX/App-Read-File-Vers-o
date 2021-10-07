<?php

    $rota = $_GET['url'] ?? 'auth';

    //var_dump($rota);
    include 'source/Models/AuthTokenJWT.php';
    include 'source/Models/UUID.php';
    include 'source/Models/readFile.php';
    include 'source/Models/Array2XML.php';

    if(file_exists(__DIR__ . "/source/Controllers/{$rota}.php")){
        if($rota == 'auth'){
            include "source/Controllers/{$rota}.php";
            $response = auth();

            if(isset($response)){
                http_response_code(200);
                header('Content-Type: application/json; charset=utf-8');
                header("Authorization: Bearer $response");
                echo json_encode(array("status"=>"logged"));
            }else{
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array("status"=>"unauthorized"));
            }
        }elseif ($rota === 'read-file'){
            include "source/Controllers/{$rota}.php";
            $headers=getallheaders();

            $return = AuthTokenJWT::checkAuth($headers['Authorization']);

            if($return){
                if($return === 'expired'){
                    http_response_code(401);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(array("status"=>"token expired"));
                }else{
                    $fileRead = $_FILES['fileRead'];
                    $fileTemplate = $_FILES['fileTemplate'];
                    $lengthTemplate = $_POST['lengthTemplate'] ?? 1;
                    $delimiter= $_POST['delimiter'] ?? ",";
                    $offset = $_POST['offset'] ?? 0;
                    $parse = $_POST['parse'] ?? "json";
                    $dirUpload = "./source/Controllers";

                    if(move_uploaded_file($fileRead["tmp_name"], "$dirUpload/".$fileRead["name"]) && move_uploaded_file($fileTemplate["tmp_name"], "$dirUpload/".$fileTemplate["name"]) && !empty($lengthTemplate)) {
                        read($fileRead["name"], $fileTemplate["name"], $lengthTemplate, $delimiter, $offset, $parse);
                    }else{
                        http_response_code(400);
                        if($parse == 'txt') {
                            header('Content-Type: application/txt; charset=utf-8');
                            echo "Ocorreu um erro durante upload do arquivos.\n";
                        }elseif($parse == 'json') {
                            $errors=array("error"=>"Ocorreu um erro durante upload do arquivos.");
                            $response['response']["errors"]=array_map(null,$errors);
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode($response);
                        }else{
                            $errors=array("error"=>"Ocorreu um erro durante upload do arquivos.");
                            $response['response']["errors"]=array_map(null,$errors);
                            unset($array);
                            $json=json_encode($response['response']);
                            $array = json_decode($json,true);

                            $xml = new SimpleXMLElement('<root/>');

                            function array2xml($array, $xml = false){
                                if($xml === false){
                                    //$xml = new SimpleXMLElement('<result/>');
                                    $xml = new SimpleXMLElement('<response/>');
                                }

                                foreach($array as $key => $value){
                                    if(is_array($value)){
                                        array2xml($value, $xml->addChild($key));
                                    } else {
                                        $xml->addChild($key, $value);
                                    }
                                }

                                return $xml->asXML();
                            }

                            $xml=array2xml($array,false);
                            header('Content-Type: text/xml; charset=utf-8');
                            echo $xml;
                        }
                    }
                }
            }else{
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array("status"=>"unauthorized"));
            }
        }else{
            http_response_code(400);
            $errors=array("error"=>"Access deined.");
            $response['response']["errors"]=array_map(null,$errors);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
        }
    }else{
        http_response_code(404);
        $errors=array("error"=>"No Route matched with those values.");
        $response['response']["errors"]=array_map(null,$errors);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
    }

