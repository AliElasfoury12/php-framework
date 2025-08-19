<?php 

namespace core;

class Preformance {
    public static function time($fun): float 
    {
        $startTime = microtime(true);
        call_user_func($fun);
        $endTime = microtime(true);
        return $endTime - $startTime;
    }
}