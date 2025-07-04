<?php 

namespace core\request;

use core\App;

class RateLimiter {

    private static $limit = 0;
    private static $unit = 'minute';

    public static function setLimit(int $limit, string $unit = 'minute') 
    {
        self::$limit = $limit;
        self::$unit = $unit;
    }

    public function limit (): void 
    {
        date_default_timezone_set($_ENV['Time_Zone']);

        $ip = $this->GetUserIP();
        $users = $this->GetUsersData();
        $user = &$users[$ip];

        if((array) $user) {
           $this->HandleExsistingUser($user, $users);
        }else{
            $users[$ip] = ['requestsNo' => self::$limit-1 , 'time' => date('Y-m-d H:i:s')];
            file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
        }
        
    } 

    private function GetUserIP () 
    {
        if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
            return  $_SERVER['HTTP_X_FORWARDED_FOR'];
        else return $_SERVER['REMOTE_ADDR'];
    }

    private function GetUsersData (): array 
    {
        $users =  file_get_contents(__DIR__.'/RateLimiter.json');
        $users = (array) json_decode($users);
        return $users;
    }

    private function HandleExsistingUser (Object $user, array $users): void 
    {
        $requestNo = &$user->requestsNo;

        if($requestNo <= 0) {
            $this->BlockUnBlockUser($user, $users);
        }else{
            $requestNo--;
            file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
        }
    }

    private function BlockUnBlockUser (Object $user, array $users): void 
    {
        $timeAgo = $this->CalculateTimeFromFirstRequest($user); 
    
        if($timeAgo < 1) exit ('Too Many Requests');
        else{
            $user->time = date('Y-m-d H:i:s');
            $user->requestsNo = self::$limit;
            file_put_contents(__DIR__.'/RateLimiter.json',json_encode($users, JSON_PRETTY_PRINT));
        }
    }

    private function CalculateTimeFromFirstRequest (Object $user): int 
    {
        $startDate = new \DateTime($user->time);
        $sinceStart = $startDate->diff(new \DateTime());
        
        if(self::$unit == 'hour') $timeAgo = $sinceStart->h;
        if(self::$unit == 'minute') $timeAgo = $sinceStart->i;
        return $timeAgo;
    }   
}