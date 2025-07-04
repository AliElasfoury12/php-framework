<?php 

namespace core;

use core\base\_Array;
use core\request\RateLimiter;

class Middleware {
    public _Array $middlewares; 
    public array $groupMiddleware = []; 

    public function __construct() {
        $this->middlewares = new _Array;
    }

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

    public function handleMiddleWares(string $method, string $route) :void 
    {

        $middlewares = $this->middlewares->$method->$route;
        App::dump([$middlewares, $route]);
        if($middlewares) {
            foreach ($middlewares as $middleware) {
                call_user_func(['core\Middleware', $middleware]);
            }
        }
    }

    public function createMiddelwares (array $middlewares, string $method, string $path):void 
    {
        //middlewares for one path
        if(!$this->middlewares->$method) $this->middlewares->$method = new _Array();
        foreach ($middlewares as $middleware) {
            if($this->middlewares->$method->$path){
                $this->middlewares[$method][$path][] = $middleware;
            }else {
                $this->middlewares[$method][$path] = new _Array([$middleware]);
            }
        } 
    }
    
} 

/*
  $middlewares = [
  'get' => [
       'route1' => ['m1', 'm2'],
        'route2' => ['m1', 'm2']
  ],
  'post' => []
  ]
  
 */