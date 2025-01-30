<?php 

namespace core\database\migrations\table;

trait Modifiers
{
    public function after ($column) 
    {
        $lastItem = $this->getLastItem();
        $this->setLastItem("$lastItem AFTER $column");      
        return $this;
    }

    public function cascadeOnDelete ( ) {
        $lastItem = $this->getLastItem();
        $lastItem ="$lastItem ON DELETE CASCADE";
        $this->setLastItem($lastItem);
        return $this ;
    }

    public function constrained ($table = '', $key = '') {
        $lastItem = $this->getLastItem();
        if($key){
            $key == $key; 
        }else {
            $key = 'id';
        }

        $name = str_replace('BIGINT(20) UNSIGNED NOT NULL','',$lastItem);
        $tableName = self::$tableName;
        $index =  "$tableName"."_$name";

        if(!$table){
            $table = str_replace('_id ','',$name);
            $table = "$table"."s";
        }

        $this->addField("CONSTRAINT $index FOREIGN KEY ($name) REFERENCES $table ($key)");
        return $this ;
    }

    public function default ($default) {
        switch ($default) {
            case false:
                $default = 0;
            break;
            case true:
                $default = 1;
            break;
        }
        $item = $this->getLastItem();
        $item = str_replace('NOT NULL', ' ', $item);
        $this->setLastItem("$item DEFAULT $default"); 
        return $this ;
    }

    public function dropColumn ($column) {
        $this->query['drop'][] = "DROP COLUMN $column";
        return $this;
    }

    public function foreign ($name)
    {
        $tableName = self::$tableName;
        $this->addField("$tableName".'_'."$name FOREIGN KEY ($name)");
        return $this ;
    }
    public function json ($name)
    {
        $this->addField("$name LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid($name))");
        return $this ;
    }
  
    public function nullable () {
        $lastItem = $this->getLastItem();
        $lastItem = str_replace('NOT', '',  $lastItem);
        $this->setLastItem($lastItem);
        return $this;
    }

    public function on ($table) 
    {
        $lastItem = $this->getLastItem();
        array_pop($this->query['add']);
        $this->addField(str_replace('{table}', $table, $lastItem));
        return $this;
    }

    public function primary () 
    {
        $lastItem = $this->getLastItem();
        $lastItem = "$lastItem PRIMARY KEY";
        $this->setLastItem($lastItem);
        return $this;
    }

    public function references ($column = '') 
    {
        $lastItem = $this->getLastItem();
        array_pop(  $this->query['add']);
        if(!$column){
            $column = 'id';
        }

        $this->addField("CONSTRAINT $lastItem REFERENCES {table} ($column)");
        return $this ;
    }

    public function unique ( ) {
        $lastItem = $this->getLastItem();
        $lastItem = "$lastItem UNIQUE";
        $this->setLastItem($lastItem);
        return $this;
    }

    public function unsigned ( ) {
        $lastItem = $this->getLastItem();
        $lastItem = str_replace('NOT NULL','UNSIGNED NOT NULL', $lastItem);
        $this->setLastItem($lastItem);
        return $this;
    }
}
