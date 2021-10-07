<?php
    //header('Content-Type: application/text; charset=utf-8');
    include '../Models/readFile.php';


   /*
    $fileRead = $_FILES['fileRead'];
    $fileTemplate = $_FILES['fileTemplate'];
    $lengthTemplate = $_POST['lengthTemplate'] ?? 1;
    $delimiter= $_POST['delimiter'] ?? ",";
    $offset = $_POST['offset'] ?? 0;
    $parse = $_POST['parse'] ?? "txt";
    $response = array();
    $array = array();
*/
    function read($fileRead, $fileTemplate,  $lengthTemplate, $delimiter, $offset, $parse){
        $readFile = new \readFile();

        $response = array();
        $array = array();

        $data = $readFile->parse_csv_file(__DIR__ . '/' . $fileTemplate, $delimiter);

        $readFile->write_ini_file($data, __DIR__ . '/templates.ini', true);

        define("template", parse_ini_file(__DIR__ . '/templates.ini', true));
        $fileName = $fileRead;

        $response['response'] = array();
        //$data= array();

        /**
         * @return Generator
         */
        $fileData = function() use ($fileName) {
            $file = fopen(__DIR__ . '/'. $fileName , 'r');

            if (!$file)
                die('file does not exist or cannot be opened');

            while (($line = fgets($file)) !== false) {
                yield $line;
            }

            fclose($file);
        };
        $start=0;
        $end=0;
        $i=0;
        foreach ($fileData($fileName) as $line){
            $pos=substr($line,$offset,$lengthTemplate);
            if(isset(template["$pos"])) {
                foreach (template["$pos"] as $key => $value) {
                    $z = substr($line, $offset, $lengthTemplate);
                    $start = ($end + 1);
                    $end = (($start - 1) + $value);
                    $x = substr($line, ($start - 1), $value);
                    if($parse == 'txt'){
                        header('Content-Type: application/text; charset=utf-8');
                        printf("%s[%s]=\"%s\"\n", $key, $value, $x);
                    }elseif ($parse == 'json'){
                        $array=array_map(null,
                            array("atributo"=>$key,
                                "tamanho"=>$value,
                                "valor"=>$x)
                        );
                        $response['response'][$i][]=array_map(null,$array);
                    }else{
                        $array=array_map(null,
                            array("atributo"=>$key,
                                "tamanho"=>$value,
                                "valor"=>$x)
                        );
                        $response['response'][$i][]=array_map(null,$array);
                    }
                }
                if($parse == 'txt'){
                    http_response_code(200);
                    printf("\n%s\n",str_pad("",150,"-"));
                }
                $i+=1;
                $start=0;
                $end=0;

            }else{
                http_response_code(400);
                if($parse == 'txt') {
                    printf("Template[%s], não foi encontrado em template.ini .\n", $pos);
                }elseif($parse == 'json') {
                    $errors=array("error"=>"Template[{$pos}], não foi encontrado em template.ini .");
                    $response['response']["errors"][]=array_map(null,$errors);
                }else{
                    $errors=array("error"=>"Template[{$pos}], não foi encontrado em template.ini .");
                    $response['response']["errors"][]=array_map(null,$errors);
                }
            }
        }
        unlink(__DIR__ . '/' . $fileName);
        unlink(__DIR__ . '/' . $fileTemplate);
        unlink(__DIR__ . '/templates.ini');

        if ($parse === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
        }elseif ($parse === 'xml'){
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