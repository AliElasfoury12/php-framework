<?php 

namespace core;

use core\request\RateLimiter;

class Middleware {
    protected array $middlewares = []; 
    protected array $groupMiddleware = []; 

    public static function auth () {
        if(!(array)App::$app->user){
            echo '403 | Unuathorized';
            exit;
        }
    }

    public static function apiAuth () {
        $token = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace('Bearer ', '', $token);

        $sql = "SELECT token FROM accessTokens WHERE token = '$token'";
        $result = App::$app->db->query($sql);

        if(!$result) {
            echo '403 | Unauthrized';
            exit;
        }
    }

    public static function limit () {
       return RateLimiter::limit();
    }

    public function handleMiddleWares(string $method, string $route) :void 
    {
        if($this->middlewares){
            if(array_key_exists($method,$this->middlewares)){
                if (array_key_exists($route, $this->middlewares[$method])) {
                    $middlewares = $this->middlewares[$method][$route];

                    if($middlewares) {
                        foreach ($middlewares as $middleware) {
                            call_user_func(['core\Middleware', $middleware]);
                        }
                    }
                }
            }
        }
    }

    public function createMiddelwares (array $middlewares, string $method, string $path):void 
    {
        //middlewares for one path
        foreach ($middlewares as $middleware) {
            if(array_key_exists($method, $middlewares)){
                if(array_key_exists($path, $this->middlewares[$method])) {
                    $this->middlewares[$method][$path][] = $middleware;
                }
            }else {
                $this->middlewares[$method][$path] = [$middleware];
            }
        } 
    }
    
} 

/**
 * $middlewares = [
 * 'get' => [
 *      'route1' => ['m1', 'm2'],
 *       'route2' => ['m1', 'm2']
 * ],
 * 'post' => []
 * ]
 * 
 */