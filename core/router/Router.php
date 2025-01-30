<?php

namespace core\router;

use core\App;
use core\Middleware;

class Router extends Middleware {
    use HandleURLTrait, HandleCalbackTrait, MethodsTrait;
    public  array  $routes = [];
    private string $lastPath = '';
    private string $lastMethod = '';

    public function resolve () 
    {
        $request = &App::$app->request;
        $path = $request->getPath();
        $method = $request->method();

        $result = $this->handleCallback( $method, $path);
        $callback = $result['callback'];
        $vars = $result['vars'];

        $arges = $this->addArgs($request,$vars);

        return call_user_func_array ($callback, $arges);
    }

    private function addArgs ($request, $vars) 
    {
        $arges = [$request];

        if($vars) $arges = array_merge($arges, $vars);

        if(array_key_exists('QUERY_STRING', $_SERVER)) 
        {
            parse_str($_SERVER['QUERY_STRING'], $queryString);
            if(array_key_exists('page', $queryString)) {
                App::$app->model->pageNum = (int) $queryString['page'];
            };

            $arges = array_merge($arges, array_values($queryString));
        }

        return $arges;
    }

    public function redirect (string $url){
        header("Location: $url");
    }

    public function middelware (string $middlewares):void 
    {
        $middlewares = explode('|', $middlewares);
        $this->createMiddelwares($middlewares,$this->lastMethod,$this->lastPath);
    }

    public static function middelwareGroup (string $middlewares)
    {
        $router = App::$app->router;
        $router->groupMiddleware = explode('|', $middlewares);
        return $router;
    }

}