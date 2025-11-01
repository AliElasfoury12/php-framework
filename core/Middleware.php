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

    public function handleMiddleWares(string $method, string $route) :void 
    {
        $middlewares = $this->middlewares->$method->$route;
        if($middlewares) {
            foreach ($middlewares as $middleware) {
                call_user_func(['app\Middlewares\MainMiddlewares', $middleware]);
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