<?php 

namespace core\router;

use core\App;

trait MethodsTrait {

    private static string $controller = '';

    public static function get($path, $callback){   
        return self::handleMethod($path, $callback, 'get');
    }

    public static function post($path, $callback){
        return self::handleMethod($path, $callback, 'post');
    }

    public static function put($path, $callback){
        return self::handleMethod($path, $callback, 'put');
    }

    public static function delete($path, $callback){
        return self::handleMethod($path, $callback, 'delete');
    }

    private static function handleMethod($path, $callback, $method) 
    {
        $router = &App::$app->router;

        if($router->groupMiddleware) {
            $router->createMiddelwares($router->groupMiddleware, $router->lastMethod,  $router->lastPath);
        }

        $router->lastPath = $path;
        $router->lastMethod = $method;

        if(self::$controller){
            $router->routes[$method][$path] = [self::$controller, $callback];
        }else {
            $router->routes[$method][$path] = $callback;
        }
     
        return  $router; 
    }

    public static function controller($controller) {
        self::$controller = $controller;
        return App::$app->router;
    }

    public function group ($callback) {
        $callback();
        self::$controller = '';
        $this->groupMiddleware = [];
    }
}