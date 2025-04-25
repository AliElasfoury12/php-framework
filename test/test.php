<?php 

//echo "\n \033[36;44m INFO \033[0m\n\n";

$a = [1,2,3,4];

$i = 0;
foreach ($a as $key => &$value) {
    if($i == 2) $a[] = 7;
    echo $value;
    $i++;
}

