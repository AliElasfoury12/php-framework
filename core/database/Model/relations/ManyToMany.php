<?php 

namespace Core\Database\Model\Relations;

use core\App;

class ManyToMany extends Relations{

    public static function run ($table1, $table2, $pivotKey, $relatedKey) //followers, user_id, follower_id 
    {
        $model= App::$app->model;
        
        if(!$model->relationData) {
            $query = $model->getQuery();
            $select = $model->handleSelect();

            $sql = "SELECT $select FROM  $table1 $query";
            $model->query = [];
            $model->relationData = $model->fetch($sql);
        }

        $primaryKey1 = $model->getPK($table1);
        $requestedCoulmns = $model->getRequestedColumns($table1);

        foreach ($model->relationData as &$result) {
            $id = $result[$primaryKey1];

            $sql = "SELECT $requestedCoulmns FROM $table1
            JOIN $table2 ON $table2.$relatedKey = $table1.$primaryKey1
            WHERE $table2.$pivotKey = $id";

            $result[$model->relationName] = $model->fetch($sql);
        }
    }

    public static function nested ($table1, $table2, $relation1, $relation2, $primaryKey, $relatedKey, $pivotKey): void
    {
        $model = App::$app->model;
        if(array_key_exists("$relation1.$relation2",$model->requestedCoulmns)){
            $columns = $model->requestedCoulmns["$relation1.$relation2"];
            $columns = explode(',', $columns);
            $columns = array_map(fn($c) => "$table1.$c" ,$columns);
            $columns = implode(',', $columns);
        }else $columns = "$table1.*";
        

        $sql = "SELECT $columns FROM $table1
        JOIN $table2 ON $table2.$relatedKey = $table1.$primaryKey
        WHERE $table2.$pivotKey = :id";

        foreach ($model->relationData as &$items) {
            $items = &$items[$relation1];
            if(array_key_exists($primaryKey, $items)){
                $id = $items[$primaryKey];
                $sql = str_replace(':id', $id, $sql);
                $items[$relation2] = $model->fetch($sql);
            }else {
                foreach ($items as &$item) {
                    $id = $item[$primaryKey];
                    $sql = str_replace(':id', $id, $sql);            
                    $item[$relation2] = $model->fetch($sql);
                }
            }
        }
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