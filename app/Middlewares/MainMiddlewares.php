<?php

namespace app\Middlewares;

use core\App;
use core\request\RateLimiter;

class MainMiddlewares {
    public static function auth () 
    {
        if(!(array)App::$app->user){
            echo '403 | Unuathorized';
            exit;
        }
    }

    public static function apiAuth () 
    {
        $token = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace('Bearer ', '', $token);

        $sql = "SELECT token FROM accessTokens WHERE token = '$token'";
        $result = App::$app->db->fetch($sql);

        if(!$result) {
            echo '403 | Unauthrized';
            exit;
        }
    }

    public static function limit () 
    {
        $rateLimtter = new RateLimiter;
        return $rateLimtter->limit();
    }
}