<?php 

namespace core\router;

use core\App;
use core\Response;
use core\View;

trait HandleCalbackTrait {
    private View $view;

    public function handleCallback($method, $path) 
    {
        $callback = '';

        if(array_key_exists($method, $this->routes)) {
            if(array_key_exists($path, $this->routes[$method])) {
                $callback = $this->routes[$method][$path];
            }
        }

        $vars = [];

        if($callback) {
            $this->handleMiddleWares($method, $path);
        }

        if(!$callback){
            $result = $this->handleURL($method, $path);
            $callback = $result['callback'];
            $vars = $result['vars'] ;
        }

        if(!$callback){
            $reponse = App::$app->view->view('404_page');
            exit(Response::response($reponse, 404));
        }

        if(is_string($callback)){
            exit(App::$app->view->view($callback));
        }

        if (is_array($callback)){
            $callbackFun =  new $callback[0];
            $callback = [$callbackFun, $callback[1]];
        }

        return compact('callback', 'vars');
    }
}