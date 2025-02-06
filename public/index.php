<?php

    use core\App;

    require_once __DIR__."/../config/accsess.php";

    //autoloader 
    require_once __DIR__."/../vendor/autoload.php";

    //dotENV
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

   // ini_set('display_errors', '1');
    //error_reporting(E_ALL);

    //intialize app
    try { 
        $app = new App();

        $routes = scandir(__DIR__.'/../routes');
        foreach ($routes as $route) {
            if($route == '..' || $route == '.') continue;
            require_once __DIR__."/../routes/$route";
        }
        $app->run();
    } catch (Error $e) {
       App::displayError($e);
    }

    