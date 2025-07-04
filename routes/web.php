<?php

use app\controllers\mvc\AuthController as MvcAuthController;
use app\controllers\PostController;
use app\controllers\UserController;
use core\App;
use core\request\RateLimiter;
use core\router\Route;

RateLimiter::setLimit(5);

Route::get('/', function () {
    $user = App::$app->user;
    if(!$user->isGuest()) {
      return App::$app->view->layoutView('home', compact('user'));
    }
    return App::$app->router->redirect('/login');
});

Route::get('/profile', function() {
    $user = App::$app->user;
    return App::$app->view->layoutView('profile', compact('user'));
}); 

Route::get('/posts', [PostController::class, 'index']);
Route::get('/users', [UserController::class, 'index'])->middelware('limit');
Route::get('/server/{id}', fn($request,$id) => $id )->middelware('limit'); 

Route::controller( MvcAuthController::class)
->middelwareGroup('limit|auth')->group(function ()
{
    Route::post('/register', 'register');
    Route::get('/register', 'register');
    Route::get('/login', 'login');
    Route::post('/login', 'login');
    Route::get('/logout', 'logout');
});
