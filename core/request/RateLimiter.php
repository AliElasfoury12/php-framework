<?php 

namespace core\request;

class RateLimiter {

    private static $limit = 0;
    private static $unit = 'minute';

    public static function setLimit($limit, $unit = 'minute') {
        self::$limit = $limit;
        self::$unit = $unit;
    }

    public static function limit () {
        date_default_timezone_set($_ENV['Time_Zone']);

        $ip = '';
        if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
            $ip =  $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else $_SERVER['REMOTE_ADDR'];

        $users = file_get_contents(__DIR__.'/RateLimiter.json');
        $users = (array) json_decode($users);
        $user = &$users[$ip];

        if($user) {
            $requestNo = &$user->requestsNo;
    
            if($requestNo == 0) {
                $startDate = new \DateTime($user->time);
                $sinceStart = $startDate->diff(new \DateTime());
                
                if(self::$unit == 'hour') $timeAgo = $sinceStart->h;
                if(self::$unit == 'minute') $timeAgo = $sinceStart->i;
                
                if($timeAgo >= 1){
                    $user->time = date('Y-m-d H:i:s');
                    $requestNo = self::$limit-1;
                    file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
                }

                if($timeAgo < 1) exit ('Too Many Requests');
            }

            if($requestNo > 0) {
                $requestNo--;
                file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
            }
        }
        
        if(!$user) {
            $users[$ip] = ['requestsNo' => self::$limit-1 , 'time' => date('Y-m-d H:i:s')];
            file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
        }
    } 
}