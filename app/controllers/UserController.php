<?php

namespace app\controllers;

use core\App;
use core\Response;
use app\models\User;

class UserController extends Controller {

    public function index () 
    {
        $res = User::select('id,name,email')::with(['posts','followings:id,name']);
        App::dump($res);
      // App::dump(User::select('id,name')->limit(1)->with(['posts:post']) );
      //return Response::json(User::all());
    }

}