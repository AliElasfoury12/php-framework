<?php

namespace core\Database\migrations\table;

class Table extends Columns {
   
    public string $name = '';
    public Query $query;
    public static Table $table;

    public function __construct() 
    {
        $this->query = new Query;
        self::$table = $this;
    }
}