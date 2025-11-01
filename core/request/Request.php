<?php

namespace core\request;

use core\base\_Array;

class Request  {
  
    public function getPath (): string 
    {
        $path = $_SERVER['REQUEST_URI'];

        if($path == '/') return  '/';    

        preg_match("/([(\w+)\/]+)\?/",$path, $match);
        if($match) $path = $match[1];
        $path = chop($path,'/');
        
        return $path;
    }

    public function method (): string 
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet (): bool 
    {
        return $this->method() === 'get';
    }

    public function isPost (): bool 
    {
        return $this->method() === 'post';
    }

    public function getBody (): _Array 
    {
        $body = new _Array;

        if($this->isGet()){
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if($this->isPost()){
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }

    public function validate (array $rules) {
        $body = $this->getBody();
        $errors =  Validator::check($body, $rules);

        if($errors) return;
        return $body;
    }
}