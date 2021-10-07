<?php

    include '../Models/AuthTokenJWT.php';

    $authTokenJWT = new \AuthTokenJWT();

    $headers=getallheaders();

    $return = AuthTokenJWT::checkAuth($headers['Authorization']);

    if($return){
        if($return === 'expired'){
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array("status"=>"token expired"));
        }else{
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array("status"=>"authorized"));
        }
    }else{
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array("status"=>"unauthorized"));
    }