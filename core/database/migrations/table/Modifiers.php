<?php 

namespace core\database\migrations\table;

class Modifiers
{
    private function table (): Table
    {
        return Table::$table;
    }

    private function query (): Query
    {
        return $this->table()->query;
    }

    public function after (string $column): Table 
    {
        $this->query()->concateLast("AFTER $column");      
        return $this->table();
    }

    public function cascadeOnDelete (): Table 
    {
        $this->query()->concateLast("ON DELETE CASCADE");      
        return $this->table();
    }

    public function constrained (string $table = '', string $key = 'id') 
    {
        $lastItem = $this->query()->last();
   
        $name = str_replace('BIGINT(20) UNSIGNED NOT NULL','',$lastItem);
        $index =  $this->table()->name."_$name";

        if(!$table){
            $table = str_replace('_id ','',$name);
            $table .= 's';
        }

        $this->query()->add("CONSTRAINT $index FOREIGN KEY ($name) REFERENCES $table ($key)");
        return $this->table();
    }

    public function default (mixed $default): Table 
    {
        match ($default) {
            false => $default = 0 ,
            true => $default = 1,
            default => $default 
        };

        if(is_string($default)) $default = "'$default'";

        $item = $this->query()->last();
        $item = str_replace('NOT NULL', ' ', $item);
        $this->query()->setLast("$item DEFAULT $default"); 
        return $this->table();
    }

    public function dropColumn (string $column): Table 
    {
        $this->query()->drop("DROP COLUMN $column");
        return $this->table();
    }

    public function foreign (string $name): Table
    {
        $this->query()->add($this->table()->name.'_'."$name FOREIGN KEY ($name)");
        return $this->table();
    }
    public function json (string $name): Table
    {
        $this->query()->add("$name LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid($name))");
        return $this->table();
    }
  
    public function nullable (): Table 
    {
        $lastItem = $this->query()->last();
        $lastItem = str_replace('NOT', '',  $lastItem);
        $this->query()->setLast($lastItem);
        return $this->table();
    }

    public function on (string $table): Table 
    {
        $lastItem = $this->query()->last();
        $this->query()->pop();
        $this->query()->add(str_replace('{table}', $table, $lastItem));
        return $this->table();
    }

    public function primary (): Table 
    {
        $this->query()->concateLast('PRIMARY KEY');
        return $this->table();
    }

    public function references (string $column = 'id'): Table 
    {
        $lastItem = $this->query()->last();
        $this->query()->pop();
     
        $this->query()->add("CONSTRAINT $lastItem REFERENCES {table} ($column)");
        return $this->table();
    }

    public function unique (): Table 
    {
        $lastItem = $this->query()->last();
        $this->query()->setLast("$lastItem UNIQUE");
        return $this->table();
    }

    public function unsigned ( ) {
        $lastItem = $this->query()->last();
        $lastItem = str_replace('NOT NULL','UNSIGNED NOT NULL', $lastItem);
        $this->query()->setLast($lastItem);
        return $this->table();
    }
}