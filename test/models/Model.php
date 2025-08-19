<?php 

use core\App;

class Model {
    public string $table;
    public string $primaryKey;
    public array $relations = [];
    public array $data = [];


    public function __construct() {
        $this->table = $this->getTable($this::class);
        $this->primaryKey = App::$app->db->getPK($this->table);
    }

    public function getTable(string $class): string 
    {
        return $this->ToCamelCase($class).'s';
    }

    private function ToCamelCase (string $string): string 
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
}