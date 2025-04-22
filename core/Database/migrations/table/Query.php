<?php 

namespace core\Database\migrations\table;

use core\base\_Array;

class Query {
    public _Array $create ;
    public _Array $drop ;

    public function __construct() 
    {
        $this->create = new _Array();
        $this->drop = new _Array();
    }

    public function add (string $field): void 
    {
        $this->create[] = $field;
    }

    public function drop (string $field): void 
    {
        $this->drop[] = $field;
    }

    public function pop (): void
    {
        $this->create->pop();
    }

    public function last ():string 
    {
        return $this->create[$this->create->size - 1];
    }

    public function setLast (string $value): void 
    {
        $this->create[$this->create->size  - 1] = $value;
    }

    public function concateLast (string $value): void 
    {
        $this->create[$this->create->size  - 1] .= " $value";
    }
}