<?php

namespace core;

class Response  {

    public function setStatusCode (int $code) {
        http_response_code($code);
    }

    public static function response ($response, $statusCode = 200) {
        http_response_code($statusCode);
        return $response;
    }

    public static function json ($response, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($response, JSON_PRETTY_PRINT);
    }

}