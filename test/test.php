<?php 

class Test {
    public $string ='';

    public function __construct($string = '') 
    {
        echo "Construct \n";
        $this->string = $string;
    }

    public function replace (string $search, string $replace): Test
    {
        $result = str_replace($search, $replace, $this->string);
        return new self($result);
    }
}

$string = new Test('ali ');