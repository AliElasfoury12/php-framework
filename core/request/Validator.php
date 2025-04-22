<?php 

namespace core\request;

use core\Database\DB;

class Validator 
{
    private static $errors = [];
    private static $errorsMessages = [];

    public static function check (object $body, array $rules) {

        foreach ($body as $field => $value) {
            
            $rules[$field] = explode('|', $rules[$field]);

            foreach ($rules[$field] as $rule) {

                $rule = str_replace(' ', '',$rule);

                $rule == 'required' ? self::required($field,$value, $rule) : '';

                str_contains($rule,'min') ? self::min($field,$value, $rule) : '';

                str_contains($rule,'max') ? self::max($field,$value, $rule) : '';

                $rule == 'email' ? self::email($field,$value, $rule) : '';

                str_contains($rule,'unique') ? self::unique($field,$value, $rule) : '';

                str_contains($rule,'match') ? self::match($field,$value, $rule, $body) : '';
 
                $rule == 'password' ? self::password($field,$value, $rule) : '';
            }
        } 

        if(self::$errors){
            return self::getErrorMessages();
        }
    }

    public static function getErrorMessages () {
        foreach (self::$errors as $field => $message) {
            self::$errorsMessages[$field] = self::$errors[$field][0];
        }
        return self::$errorsMessages;
    }

    public static function addErrorMessage ($field, $message) {
        if(self::$errors[$field]){
            self::$errors[$field][] = $message;
        }
        else{
            self::$errors[$field] = [$message];
        }
    }

    private static function required ($field, $value, $rule) {
        if($value == ''){
            $message = "$field is required";
            self::addErrorMessage($field,$message);
        }
    }

    private static function email ($field, $value, $rule) {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
            $message = "$field is Not a Valid Email";
            self::addErrorMessage($field,$message);
        }
    }

    private static function unique ($field, $value, $rule) {
        $table = str_replace('unique:', '', $rule);
        $exsists =  DB::table($table)->select($field)->where($field, $value)->get();
        $message = "$field Must be Unique";
        if($exsists){
            self::addErrorMessage($field,$message);
        }
    }

    private static function password ($field, $value, $rule) {
        $message = "$field is Not a Valid password";
        self::addErrorMessage($field,$message);
    }

    private static function match ($field, $value, $rule, $body) {
        preg_match("/match:\s*(\w+)/",$rule, $match);
        $match = $match[1];

        if($body->$match != $value){
            $message = "$field don't Match $match";
            self::addErrorMessage($field,$message);
        }
    }

    private static function min ($field, $value, $rule) {
        preg_match("/min:\s*(\d+)/",$rule, $match);
        $min = $match[1];

        if(strlen($value) < $min){
            $message = "$field must be at least $min characters long";
            self::addErrorMessage($field,$message);
        }
    }


    private static function max ($field, $value, $rule) {
        preg_match("/max:\s*(\d+)/",$rule, $match);
        $max = $match[1];

        if(strlen($value) > $max){
            $message = "$field must be less than $max characters long";
            self::addErrorMessage($field,$message);
        }
    }
}