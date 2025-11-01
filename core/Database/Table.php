<?php

namespace core\Database;

use core\base\_Array;

class Table
{
    public string $name = '';
    public string $PK = '';
    public _Array $FKS;

    public function __construct(string $name) {
        $this->name = $name;
        $this->FKS = new _Array;
    }
}