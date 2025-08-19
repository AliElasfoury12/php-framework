<?php 

use core\base\_Array;
use core\Database\Model\Query\Query;

//Model extends MainModel extends QueryBuilder extends QueryExexcution
class Model {
    public Query $query = [/*$where, $select, $extraQuery, $finalQuery */];
    public _Array $relations;
}