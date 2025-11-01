<?php 

namespace core\Database\migrations\table;

class Columns extends Modifiers
{
    private function table (): Table
    {
        return Table::$table;
    }

    private function query (): Query
    {
        return $this->table()->query;
    }

    public function bigInt (string $name): Table
    {
        $this->query()->add("$name BIGINT NOT NULL");
        return $this->table();
    }

    public function bool (string $column): Table
    {
        $this->query()->add("$column BOOLEAN NOT NULL");
        return $this->table();
    }

    public function foreignId (string $name): Table
    {
        $this->query()->add("$name BIGINT(20) UNSIGNED NOT NULL");
        return $this->table();
    }

    public function id (string $name = 'id'): Table
    {
        $this->query()->add("$name BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        return $this->table();
    }

    public function int (string $name): Table
    {
        $this->query()->add("$name INT NOT NULL");
        return $this->table();
    }

    public function string (string $name, int $length = 255): Table 
    {
        $this->query()->add("$name VARCHAR ($length) NOT NULL");
        return $this->table();
    }

    public function text (string $name): Table 
    {
        $this->query()->add("$name TEXT NOT NULL");
        return $this->table();
    }

    public function timeStamp (): Table 
    {
        $this->query()->add(
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" 
        );
        return $this->table();
    }
}