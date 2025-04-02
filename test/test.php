<?php

declare(strict_types=1);

//$image = file_get_contents("./D1/bicke.jpg");
//var_dump(pathinfo('https://img.freepik.com/free-photo/beautiful-selective-focus-shot-crystal-ball-reflecting-breathtaking-sunset_181624-8579.jpg'));
//file_put_contents("./D2/image2.jpg",$image );
//echo $image;

class schema  {
    public function __construct() {
        echo 'A';
    }
    public static function create () 
    {
        echo 's';
    }
}

class Table {

}

schema::create();