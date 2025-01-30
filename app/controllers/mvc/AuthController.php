<?php

namespace app\controllers\mvc;

use app\controllers\Controller;
use core\Auth;
use core\request\Validator;
use app\models\User;

class AuthController extends Controller {
    public function register () {
        if($this->isGet()) {
            if($this->auth()) {
                return $this->redirect('/');
            }
            return $this->layoutView('register');
        }
        
        $validate = $this->validate([
            'name' => 'required|min:3|max:200',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'confirmPassword' => 'required|min:4|match:password'
        ]);

        if(Validator::getErrorMessages()) {
            return $this->layoutView('register', ['user' => $this->getBody()]);
        }

        User::create($validate);
        $this->redirect('/login');
    }

    public function login () {
        if($this->isGet()) {
            if($this->auth()) 
            {
                var_dump($this->auth());
                //return $this->redirect('/');
            }
            return $this->layoutView('login');
        }

        $validate = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:4'
        ]);

        if(Validator::getErrorMessages()) {
            return $this->layoutView('register', ['user' => $this->getBody()]);
        }

        $user = User::select('id,name, email, password')
        ->where('email', $validate->email)
        ->get();

        $user = (object) $user[0];
        if(!Auth::attempt($user, $validate)) {
            return $this->layoutView('login', ['user' => $user]);
        }

       $this->session()->set('user', $user);
        return $this->redirect('/');
    }

    public function logout () {
        $this->session()->remove('user');
        return $this->redirect('/login');
    }
}