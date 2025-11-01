<?php 

namespace core\Database\Model\Query;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\MainModel;

class FinalQuery {
    public string $select = '';
    public string $extraQuery = '';
}

class Query {
    public _Array $where;
    public _String $select;
    public _Array $extraQuery;
    public FinalQuery $finalQuery;

    public function __construct() {
        $this->where = new _Array();
        $this->select = new _String();
        $this->extraQuery = new _Array();
        $this->finalQuery = new FinalQuery;
    }

    public function getQuery (string $table = ''): string
    {
        if(!$this->where->empty()) {
            $this->finalQuery->extraQuery = ' WHERE ';
            if($table) $this->where->map(fn($w) => "$table.$w");

            if(!$this->where->empty()) 
                $this->finalQuery->extraQuery .= $this->where->implode(' AND ');
            else 
                $this->finalQuery->extraQuery .= $this->where[0];
        }

        if(!$this->extraQuery->empty()) $this->finalQuery->extraQuery .= $this->extraQuery->implode(' ');
        return $this->finalQuery->extraQuery;
    }

    public function getSelect (string $table = ''): string
    {
        if($this->select->length()){
            $select = $this->select->explode(',');
            if($table) $select = $select->map(fn($field) => "$table.$field");
            $this->finalQuery->select = $select->implode(',');
        }else $this->finalQuery->select = '*';

        if($table && !$this->select->length()) $this->finalQuery->select = "$table.*";
        return $this->finalQuery->select;
    }

}