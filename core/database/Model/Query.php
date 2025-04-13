<?php 

namespace core\database\Model;

use core\App;
use core\base\_Array;

class FinalQuery {
    public string $select = '';
    public string $extraQuery = '';

    public function reset (): void
    {
        $this->select = '*';
        $this->extraQuery = '';
    }
}

class Query {
    public _Array $where;
    public array $select = [];
    public array $extraQuery = [];
    public FinalQuery $finalQuery;
   
    public function __construct() {
        $this->where = new _Array();
        $this->finalQuery = new FinalQuery;
    }

    public function reset (): void
    {
        $this->where->reset();
        $this->select = [];
        $this->extraQuery = [];
        $this->finalQuery->reset();
    }

    public function getQuery (string $table = ''): string
    {
        if(!$this->where->empty()) {
            $this->finalQuery->extraQuery = 'AND ';
            if($table) $this->where->map(fn($w) => "$table.$w");
            if($this->where->size > 1) {
                $this->finalQuery->extraQuery .= $this->where->implode('AND');
            }else {
                $this->finalQuery->extraQuery .= $this->where[0];
            }
        }

        if($this->extraQuery) $this->finalQuery->extraQuery .= implode(' ', $this->extraQuery);
        return $this->finalQuery->extraQuery;
    }

    public function getSelect (string $table = ''): string
    {
        if($this->select){
            $select = explode(',', $this->select[0]);
            if($table) $select = array_map(fn($field) => "$table.$field", $select);
            $this->finalQuery->select = implode(',', $select);
        }

        if($table && $this->select === ['*']) $this->finalQuery->select = "$table.*";
        return $this->finalQuery->select;
    }
}