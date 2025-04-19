<?php 

namespace core\database\Model\relations;

use core\database\Model\Query;

class CurrentRelation 
{
    public string $type;
    public string $table1;
    public string $table2;
    public string $primaryKey;
    public string $foreignKey;
    public string $pivotTable;
    public string $pivotKey;
    public string $relatedKey;
    public string $relation1;
    public string $relation2;
    public string $FirstSqlPart;
    public string $lastJoin_PK;
    public string $lastJoinTable;
    public string $name;
    public string $columns;
}