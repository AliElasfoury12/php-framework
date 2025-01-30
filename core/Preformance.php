<?php 

namespace core;

class Preformance {
    public static function time($fun) {
        $startTime = microtime(true);
        $fun;
        $endTime = microtime(true);
        echo  round($endTime - $startTime,3)  ;
    }
}