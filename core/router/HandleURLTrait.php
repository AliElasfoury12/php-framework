<?php 

namespace core\router;

use core\App;
trait HandleURLTrait {
    public function handleURL ($method, $path) {
        $pathParts = explode('/',$path);

        foreach($this->routes[$method] as $route => $callback) {
            $routeParts = explode('/',$route);
            
            if(count($routeParts) !== count( $pathParts)){
                continue;
            }

            preg_match('/{(\w+)}/', $route, $match);
            if(!$match) {
                $vars = '';
                return compact('callback', 'vars');
            }

            $result1 = $routeParts;
            $result2 = $pathParts;

            for ($i=0; $i < count($pathParts) ; $i++) { 
                if($routeParts[$i] == $pathParts[$i]){
                    unset($result1[$i]);
                    unset($result2[$i]);
                }
            }

            $result = preg_replace($result1, $result2, $path);
            if($result == $path){
                $vars = $result2 ?? null;
                $this->handleMiddleWares($method, $route);
                return compact('callback', 'vars');
            }
        }
    }
}