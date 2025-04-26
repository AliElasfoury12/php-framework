<?php

namespace core\Database;

class Table
{
    public string $name = '';
    public string $PK = '';
    public array $FKS = [];

    public function __construct(string $name) {
        $this->name = $name;
    }
}