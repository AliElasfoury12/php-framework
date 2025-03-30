<?php 

namespace core\database\Model;

use core\App;

class FinalQuery {
    public string $select = '';
    public string $extraQuery = '';

    public function reset (): void
    {
        $this->select = '';
        $this->extraQuery = '';
    }
}

class Query {
    public array $where = [];
    public array $select = [];
    public array $extraQuery = [];
    public FinalQuery $finalQuery;
   
    public function __construct() {
        $this->where = [];
        $this->select = [];
        $this->extraQuery = [];
        $this->finalQuery = new FinalQuery;
    }

    public function reset (): void
    {
        $this->where = [];
        $this->select = [];
        $this->extraQuery = [];
        $this->finalQuery->reset();
    }

    public function getQuery (string $table = ''): FinalQuery
    {
        if($this->where) {
            $this->finalQuery->extraQuery = 'WHERE ';
            if($table) $this->where = array_map(fn($w) => "$table.$w", $this->where);
            if(count($this->where) > 1) {
                $this->finalQuery->extraQuery .= implode(' AND ', $this->where);
            }else {
                $this->finalQuery->extraQuery .= $this->where[0];
            }
        }
       
        if($this->extraQuery) $this->finalQuery->extraQuery .= implode(' ', $this->extraQuery);
        return $this->finalQuery;
    }

    public function handleSelect (string $table = '') 
    {
        $select = App::$app->model->query->select;
        if(isset($select)) {
            if(array_key_exists(0 , $select)) $select = $select[0];
        }

        if($select){
            if($table && $select) {
                $select = explode(',', $select);
                $select = array_map(fn($field) => "$table.$field", $select);
                $select = implode(',', $select);
            }
            return $select;
        }

        if($table) return "$table.*";

        return '*';
    }
}