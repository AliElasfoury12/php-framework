<?php 

use app\controllers\AuthController;
use core\router\Router;

//auth routes
Router::controller(AuthController::class)->group(function () {
    Router::post('/api/register', 'register');
    Router::post('/api/login', 'login');
    Router::get('/api/logout/{id}', 'logout')->middelware('apiAuth');
});