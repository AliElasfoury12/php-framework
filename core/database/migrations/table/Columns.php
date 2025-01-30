<?php 

namespace core\database\migrations\table;

trait Columns
{
    public array $query = ['add' => [], 'drop' => []];

    private function getLastItem () 
    {
        return end($this->query['add']);
    }

    private function setLastItem ($value)
    {
        $this->query['add'][count($this->query['add']) - 1] = $value;
    }

    private function addField ($field) {
        $this->query['add'][] = $field;
    }

    public function bigInt ($name)
    {
        $this->addField("$name BIGINT NOT NULL");
        return $this ;
    }

    public function bool ($column ) {
        $this->addField("$column BOOLEAN NOT NULL");
        return $this ;
    }

    public function foreignId ($name)// post_id
    {
        $this->addField("$name BIGINT(20) UNSIGNED NOT NULL");
        return $this ;
    }

    public function id ($name = 'id') 
    {
        $this->addField("$name BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        return $this ;
    }

    public function int ($name)
    {
        $this->addField("$name INT");
        return $this ;
    }

    public function string ($name, $length = 255) {
        $this->addField("$name VARCHAR ($length) NOT NULL");
        return $this ;
    }

    public function text ($name) {
        $this->addField("$name TEXT  NOT NULL");
        return $this ;
    }

    public function timesStamp () {
        $this->addField(
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" 
        );
        return $this ;
    }
}