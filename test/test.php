<?php 

$array = [
    [
        'id' => 1,
        'user_id' => 2
    ],
    [
        'id' => 3,
        'user_id' => 2
    ],
    [
        'id' => 4,
        'user_id' => 4
    ],
    [
        'id' => 5,
        'user_id' => 2
    ],
    [
        'id' => 6,
        'user_id' => 3
    ],
    [
        'id' => 7,
        'user_id' => 2
    ]
];

function insertNumber (int $newNumber, array $numbers): array 
{
    $numbers_size = count($numbers);
    $min = 0;
    $max = $numbers_size - 1;
    $middle = floor(($min + $max) / 2);
   
    while(!$numbers[$middle] < $newNumber && !$numbers[$middle + 1] > $newNumber){
        
        for ($i = $numbers_size; $i >= $middle+2 ; $i--) { 
            $numbers[$i] = $numbers[$i-1];
        }
    }
    $numbers[$middle+1] = $newNumber;

    return $numbers;
}

$numbers = [1,2,3,5,6,7];

//var_dump(insertNumber(4,$numbers));


$ids = [];
foreach ($array as $value) {
    $ids[] = $value['user_id'];
}

//print_r($ids);


