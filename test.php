<?php

declare(strict_types=1);

$array = [
    [
        'pivot' => 1,
        'id' => 1,
        'name' => 'image1'
    ],[
        'pivot' => 1,
        'id' => 2,
        'name' => 'image2'
    ],[
        'pivot' => 2,
        'id' => 3,
        'name' => 'image3'
    ],[
        'pivot' => 3,
        'id' => 4,
        'name' => 'image4'
    ],[
        'pivot' => 3,
        'id' => 5,
        'name' => 'image5'
    ]
];

$result = [];

foreach ($array as $value) {
   if(@$result[$value['pivot']]){
    $result[$value['pivot']][] = $value;  
   }else {
    $result[$value['pivot']] = [$value];  
   }
}

print_r($result);