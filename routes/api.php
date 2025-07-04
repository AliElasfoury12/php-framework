<?php 

use app\controllers\AuthController;
use core\router\Route;

//auth routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/api/register', 'register');
    Route::post('/api/login', 'login');
    Route::get('/api/logout/{id}', 'logout')->middelware('apiAuth');
});