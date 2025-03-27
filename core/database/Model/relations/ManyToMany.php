<?php 

namespace Core\Database\Model\Relations;

use core\App;

class ManyToMany extends Relations{

    public static function run (): void //followers, user_id, follower_id 
    {
        $model= App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->currentRelation;
        $table2 = $current_relation->table2;
        $primaryKey2 = $current_relation->primaryKey;
        $pivotTable = $current_relation->pivotTable;
        $pivotKey = $current_relation->pivotKey;
        $relatedKey = $current_relation->relatedKey;
        
        $extraQuery = $model->extraQuery('alias');
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
       
        $sql = 
        "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        INNER JOIN $pivotTable ON $table1.$primaryKey1 = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias ON alias.$primaryKey2 = $pivotTable.$relatedKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
       // echo "$sql <br>"; 

        $data = $model->fetch($sql);
        $dataLength = count($data);

        $i = 0;
        foreach ($model->relationData as &$item) {
            $item[$model->relationName] = [];

            while($i < $dataLength && $item[$primaryKey1] == $data[$i]['pivot']){
                $item[$model->relationName][] = $data[$i];
                $i++;
            }
        }

        $model->query = [];

        $current_relation->FirstSqlPart = 
        "INNER JOIN $pivotTable ON $table1.$primaryKey1 = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias ON alias.$primaryKey2 = $pivotTable.$relatedKey";
        $current_relation->lastJoin_PK = $primaryKey2;
        $current_relation->lastJoinTable = $table2;
    }

    public static function nested (): void
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->currentRelation;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;
        $table2 = $current_relation->table2;
        $primaryKey2 = $current_relation->primaryKey;
        $pivotTable = $current_relation->pivotTable;
        $relatedKey = $current_relation->relatedKey;
        $pivotKey = $current_relation->pivotKey;
        $first_sql_part = $current_relation->FirstSqlPart;
        $lastTable = $current_relation->lastJoinTable;
        $lastTable_PK = $current_relation->lastJoin_PK;


        $extraQuery = $model->extraQuery('alias');
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        $sql = 
        "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $pivotTable ON $lastTable.$lastTable_PK = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias ON alias.$primaryKey2 = $pivotTable.$relatedKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
        //echo "$sql <br>"; 

        $data = $model->fetch($sql);
        $dataLength = count($data);

        //App::dump([$data]);
        $i = 0;
        foreach ($model->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($primaryKey2, $unit[$relation1])){
                $unit[$relation1][$relation2] = [];
                while($i < $dataLength && $unit[$primaryKey1] == $data[$i]['pivot']){
                    $unit[$relation1][$relation2] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                   $item[$relation2] = [];
                    while($i < $dataLength  && $item[$primaryKey1] == $data[$i]['pivot']){
                        $item[$relation2] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }

        $model->query = [];
    }
}


//get followers of user_id = 99

/*
SELECT users.*
FROM users
JOIN followers  
ON followers.follower_id = users.id
WHERE followers.user_id = 99
*/

// the users who user_id = 99 is following

/*SELECT users.*
FROM users
JOIN followers  
ON followers.user_id = users.id
WHERE followers.follower_id = 99
*/