<?php

namespace core\request;

class Request  {
  
    public function getPath () {
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        if($path == '/') return $path = '/';    

        preg_match("/([(\w+)\/]+)\?/",$path, $match);
        if($match) $path = $match[1];
        $path = chop($path,'/');
        
        return $path;
    }

    public function method () {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet () {
        return $this->method() === 'get';
    }

    public function isPost () {
        return $this->method() === 'post';
    }

    public function getBody () {
        $body = [];

        if($this->method() === 'get'){
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if($this->method() === 'post'){
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return (object) $body;
    }

    public function validate (array $rules) {
        $body = $this->getBody();
        $errors =  Validator::check($body, $rules);

        if($errors) return;
        return $body;
    }
}