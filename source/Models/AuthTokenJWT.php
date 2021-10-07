<?php

//namespace Source\Models;



class AuthTokenJWT{
    public function login(){

        include '../Models/UUID.php';

        $uuid = new \UUID();
        $iss = $uuid->getGUID();

        //Application Key
        $key = 'eyJleHAiOjE2MzM0ODgzMDMsImdlbiI6MTYzMzQ4NDcwMywibmFtZSI6Ik5vbWUgZG8gdXN1YXJpbyIsImVtYWlsIjoiZW1haWxAZW1haWwuY29tIn0';

        //Header Token
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        //Payload - Content
        $payload = [
            'consumer' => 'api-read-file',
            'gen' => strtotime("now"),
            'exp' => strtotime("+20 minute"),
            'iss' => $iss
        ];

        //JSON
        $header = json_encode($header);
        $payload = json_encode($payload);

        //Base 64
        $header = base64_encode($header);
        $payload = base64_encode($payload);

        //Sign
        $sign = hash_hmac('sha256', $header . "." . $payload, $key, true);
        $sign = base64_encode($sign);

        //Token
        $token = $header . '.' . $payload . '.' . $sign;

        return $token;
    }

    public static function checkAuth($token){

        if($token != null) {
            $token = explode(' ', $token);
            //$token[0] = 'Bearer';
            //$token[1] = 'Token JWT';

            $token = explode('.', $token[1]);
            //var_dump($token);exit;
            $header = $token[0];
            $payload = $token[1];
            $sign = $token[2];

            //Application Key
            $key = 'eyJleHAiOjE2MzM0ODgzMDMsImdlbiI6MTYzMzQ4NDcwMywibmFtZSI6Ik5vbWUgZG8gdXN1YXJpbyIsImVtYWlsIjoiZW1haWxAZW1haWwuY29tIn0';

            //Check Sign
            $valid = hash_hmac('sha256', $header . "." . $payload, $key, true);
            $valid = base64_encode($valid);
            $payload = json_decode(base64_decode($payload));
            $now = strtotime("now");
            if($sign === $valid){
                if($now >= $payload->exp ){
                    return "expired";
                }else{
                    return true;
                }
            }
        }

        return false;
    }
}