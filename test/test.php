<?php 

use core\base\_Array;

//echo "\n \033[36;44m INFO \033[0m\n\n";
require_once __DIR__."/../vendor/autoload.php";


$a = new _Array();


$a['d'] = ['a' => 'l'];

print_r($a->d->a);

