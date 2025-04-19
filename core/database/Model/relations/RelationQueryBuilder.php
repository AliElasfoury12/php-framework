<?php 

namespace core\database\Model\relations;

use core\database\Model\Query;
class RelationQueryBuilder
{
    public Query $query;

    public function __construct()
    {
        $this->query = new Query;
    }

    public function select (string $columns)
    {
        $this->query->select = $columns;
        return new static;
    }

    public function where (string $column ,string $opretor, string $value = ''): static 
    {
        if(!$value) {
            $value = $opretor;
            $opretor = '=';
        }
        $this->query->where[] = "$column $opretor '$value'";
        return  new static;
    }
}