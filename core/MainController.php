<?php

namespace core;

use core\request\Request;

class MainController extends Request
{
    protected function layoutView ($view, $params = [], $layout = 'main') {
        return App::$app->view->layoutView($view, $params, $layout);
    }

    protected function view ($view, $params = []) {
        return  App::$app->view->view($view, $params);
    }

    protected function redirect ($url) {
        App::$app->router->redirect($url);
    }

    protected function session () {
       return App::$app->session;
    }

    protected function auth () 
    {
        if((array) App::$app->user) 
        {
            var_dump(App::$app->user);
            return App::$app->user;
        }
        return false;
    }
     
}