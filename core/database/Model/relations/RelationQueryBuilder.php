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
}