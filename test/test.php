<?php 

//echo "\n \033[36;44m INFO \033[0m\n\n";

class Test
{
    public $class = null;

    public function __construct()
    {
        echo 'construct';
    }

    public function select ()
    {
        if($this->class == null) $this->class = new static;
        return $this->class;
    }

    public function with ()
    {
        if($this->class == null) $this->class = new static;
        echo $this->class::class;
       return $this->class;
    }
}

class Test2 extends Test
{

}

$test = new Test2();

 $test->select()->with();