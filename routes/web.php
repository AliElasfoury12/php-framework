<?php

use app\controllers\mvc\AuthController as MvcAuthController;
use app\controllers\PostController;
use app\controllers\UserController;
use core\App;
use core\request\RateLimiter;
use core\router\Router;

RateLimiter::setLimit(5);

Router::get('/', function () {
    $user = App::$app->user;
    if(!$user->isGuest()) {
      return App::$app->view->layoutView('home', compact('user'));
    }
    return App::$app->router->redirect('/login');
});

Router::get('/profile', function() {
    $user = App::$app->user;
    return App::$app->view->layoutView('profile', compact('user'));
}); 

Router::get('/posts', [PostController::class, 'index']);
Router::get('/users', [UserController::class, 'index'])->middelware('limit');
Router::get('/server/{id}', 'home')->middelware('limit'); 


Router::controller( MvcAuthController::class)
->middelwareGroup('limit')->group(function () {
    Router::post('/register', 'register');
    Router::get('/register', 'register');
    Router::get('/login', 'login');
    Router::post('/login', 'login');
    Router::get('/logout', 'logout')->middelware('auth');
});
