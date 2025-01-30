<?php

namespace app\controllers;

use core\Auth;
use core\request\Request;
use core\request\Validator;
use app\resources\UserResource;
use core\Response;
use app\models\User;

class AuthController extends Controller {
    public function register (Request $request) {
        $validate = $request->validate([
            'name' => 'required|min:3|max:200',
            'email' => 'email|required',
            'password' => 'required|min:4',
            'confirmPassword' => 'required|min:4|match:password'
        ]);

        $user = User::create($validate);
       // var_dump($user);

        $user = UserResource::toArray($user);
        return Response::json(compact('user'));
    }

    public function login (Request $request) {
        $validate = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:4'
        ]);

        $user = User::select('id,name, email, password')
        ->where('email', $validate->email)
        ->get();

        $user = (object) $user[0];
        if(!Auth::attempt($user, $validate)) {
            $errors = Validator::getErrorMessages();
            return Response::json(compact('errors'), 422);
        }

        $token = User::createAccessToken($user->id);
        $user = UserResource::toArray($user);
        $message = 'User Logged in Successfully';
       
        return Response::json(compact('message', 'user', 'token'));
    }

    public function logout (Request $request, $id) {
        $user = User::find($id); 
        User::delteAccessToken($id);

        $message = 'User Logged out Successfully';
        $user = UserResource::toArray($user);
        return Response::json(compact('message', 'user'));
    }
}