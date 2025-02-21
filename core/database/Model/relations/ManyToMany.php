<?php 

namespace Core\Database\Model\Relations;

use core\App;

class ManyToMany extends Relations{

    public static function run () //followers, user_id, follower_id 
    {
        $model= App::$app->model;
        $table1 = $model->mainTable;
        $primaryKey1 = $model->getPK($table1);

        $extraQuery = $model->extraQuery($table1);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        $table2 = $model->currentRelation['table2'];
        $pivotKey = $model->currentRelation['pivotKey'];
        $relatedKey = $model->currentRelation['relatedKey'];

        foreach ($model->relationData as &$result) {
            $id = $result[$primaryKey1];

            $sql = "SELECT $select FROM $table1
            JOIN $table2 ON $table2.$relatedKey = $table1.$primaryKey1
            WHERE $table2.$pivotKey = $id $query";

            $result[$model->relationName] = $model->fetch($sql);
        }

        $model->query = [];
    }

    public static function nested (): void
    {
        $model = App::$app->model;
        
        $relation1 = $model->currentRelation['relation1'];
        $relation2 = $model->currentRelation['relation2'];
        $table1 = $model->currentRelation['table1'];
        $table2 = $model->currentRelation['table2'];
        $primaryKey = $model->getPK($table1);
        $relatedKey = $model->currentRelation['relatedKey'];
        $pivotKey = $model->currentRelation['pivotKey'];

        $extraQuery = $model->extraQuery($table1);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        $sql = "SELECT $select FROM $table1
        JOIN $table2 ON $table2.$relatedKey = $table1.$primaryKey
        WHERE $table2.$pivotKey = :id $query";

        foreach ($model->relationData as &$items) {
            if(empty($items[$relation1])) continue;

            if(array_key_exists($primaryKey, $items)){
                $id = $items[$relation1][$primaryKey];
                $sql = str_replace(':id', $id, $sql);
                //echo "$sql <br>";
                $items[$relation1][$relation2] = $model->fetch($sql);
            }else {
                foreach ($items as &$item) {
                    $id = $item[$primaryKey];
                    $sql = str_replace(':id', $id, $sql); 
                   // echo "$sql <br>";           
                    $item[$relation1][$relation2] = $model->fetch($sql);
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