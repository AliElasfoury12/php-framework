<?php 

namespace core\Database\Model\Relations;

class CurrentRelation 
{
    public string $type;
    public string $table1;
    public string $table2;
    public string $PK1;
    public string $PK2;
    public string $FK1;
    public string $FK2;
    public string $pivotTable;
    public string $pivotKey;
    public string $relatedKey;
    public string $relation1;
    public string $relation2;
    public string $FirstSqlPart;
    public string $lastJoin_PK;
    public string $lastJoinTable ;
    public string $name;
    public string $columns;
    public string $model1;
    public string $model2;
    public string $sql;
}