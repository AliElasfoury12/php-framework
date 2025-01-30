<?php 

namespace core;

use core\database\DB;
use core\request\Validator;
use app\models\User;

trait Auth  {
    public static function createAccessToken ($id) {
        self::delteAccessToken($id);
        $token = base64_encode(openssl_random_pseudo_bytes(42));
        DB::insert('accessTokens', ['token', 'tokenable_id'], [$token, $id]);
        return $token;
    }

    public static function delteAccessToken ($id) {
        $sql = "DELETE FROM accessTokens WHERE tokenable_id = '$id'";
        DB::exec($sql);
    }

    public static function login (User $user) {
        App::$app->user = $user;
        App::$app->session->set('user', $user);
    }
 
    public static function logout ($id = '') {
        App::$app->user = null ;
        App::$app->session->remove('user');
    }
 
    public function isGuest () {
        return ! (array) App::$app->user; 
    }

    public static function attempt($user, $validate) {        
        if(!(array)$user) {
            Validator::addErrorMessage('email', 'User Not Found');
            return false;
        }
        
        if(!password_verify($validate->password, $user->password)){
            Validator::addErrorMessage('password', 'Password Incorrect');
            return false;
        }

        return true;
    }
 
}
