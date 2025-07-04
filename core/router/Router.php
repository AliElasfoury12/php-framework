<?php

namespace core\router;

use Closure;
use core\App;
use core\base\_Array;
use core\Middleware;
use core\request\Request;
use core\Response;

class Router{
    public  _Array  $routes;
    public string $lastPath = '';
    public string $lastMethod = '';

    public function __construct() {
        $this->routes = new _Array;
    }

    public function resolve () 
    {
        $request = App::$app->request;
        $path = $request->getPath();
        $method = $request->method();

        $result = $this->handleCallback($method, $path);
        $callback = $result['callback'];
        $params = $result['params'];

        $arges = $this->addArgs($request,$params);
        return call_user_func_array ($callback, $arges);
    }

    private function handleCallback(string $method, string $path): array 
    { 
        $callback = '';

        if($this->routes->$method->$path) $callback = $this->routes->$method->$path;

        $params = [];

        if(!$callback){
            $result = $this->handleURLWithParams($method, $path);
            $path = $result['route'];
            $callback = $this->routes->$method->$path;
            $params = $result['params'] ;
        }

        if(!$callback){
            $reponse = App::$app->view->view('404_page');
            exit(Response::response($reponse, 404));
        }

        if($callback) App::$app->middleware->handleMiddleWares($method, $path);

        if(is_string($callback)) exit(App::$app->view->view($callback));
        
        if ($callback instanceof _Array){
            $callback = $callback->toArray();
            $callback = [new $callback[0], $callback[1]];
        }

        return compact('callback', 'params');
    }

     private function handleURLWithParams (string $method, string $path): array 
    {
        $pathParts = explode('/',$path);

        foreach($this->routes[$method] as $route => $callback) {
            $routeParts = explode('/',$route);
            
            if(count($routeParts) !== count( $pathParts)) continue;
            
            $params = [];

            foreach ($routeParts as $i => $routePart) {
                if(preg_match('/{(\w+)}/', $routePart, $match)){
                    $params[] = $pathParts[$i];
                }else if ($pathParts[$i] !== $routePart) {
                    break;
                }
            }

            if(!$params) continue;

            return compact('route', 'params');
        }
        return [];
    }
    
    private function addArgs (Request $request, array $params): array 
    {
        $arges = [$request];

        if($params) $arges = array_merge($arges, $params);

        $arges = $this->AddQueryStringToArgs($arges);

        return $arges;
    }

    private function AddQueryStringToArgs (array $arges): array 
    {
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
}