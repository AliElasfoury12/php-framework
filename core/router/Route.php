<?php

namespace core\router;

use Closure;
use core\App;
use core\base\_Array;

class Route 
{
    private string $GroupController = '';

    public static function get(string $path, $callback): Route
    {   
        return App::$app->route->handleMethod($path, $callback, 'get');
    }

    public static function post(string $path, $callback): Route
    {
        return App::$app->route->handleMethod($path, $callback, 'post');
    }

    public static function put(string $path, $callback): Route
    {
        return App::$app->route->handleMethod($path, $callback, 'put');
    }

    public static function delete(string $path, $callback): Route
    {
        return App::$app->route->handleMethod($path, $callback, 'delete');
    }

    private static function handleMethod(string $path, $callback, string $method): Route
    {
        $router = &App::$app->router;
        $route = App::$app->route;

        if(!App::$app->middleware->groupMiddleware) 
        App::$app->middleware->createMiddelwares(App::$app->middleware->groupMiddleware,$router->lastMethod,$router->lastPath);
        
        $router->lastPath = $path;
        $router->lastMethod = $method;

        if(!$router->routes->$method) $router->routes->$method = new _Array();

        if($route->GroupController)
            $router->routes[$method][$path] = [$route->GroupController, $callback];
        else 
            $router->routes[$method][$path] = $callback;
    
        return App::$app->route; 
    }

    public static function controller($controller): Route 
    {
        $route = App::$app->route;
        $route->GroupController = $controller;
        return $route; 
    }

    public function group (Closure $callback): void 
    {
        $callback();
        App::$app->route->GroupController = '';
        App::$app->middleware->groupMiddleware = [];
    }

    public static function middelwareGroup (string $middlewares): Route
    {
        App::$app->middleware->groupMiddleware = explode('|', $middlewares);
        return App::$app->route; 
    }

    public function middelware (string $middlewares):void 
    {
        $router = App::$app->router;
        $middlewares = explode('|', $middlewares);
        App::$app->middleware
        ->createMiddelwares($middlewares,$router->lastMethod,$router->lastPath);
    }
}