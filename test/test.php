<?php

declare(strict_types=1);

namespace Test;

use core\base\_Array;
 
$array = new _Array();

$array[] = 1;
$array[] = 2;

$array->map(fn($num) => $num * 2);
print_r($array);
