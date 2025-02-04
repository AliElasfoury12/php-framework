<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run ($table1, $table2, $foreignKey , $primaryKey ) 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;

        if(!$model->relationData) {
            $query = $model->getQuery();
            $select = $model->handleSelect();
            $sql = "SELECT $select FROM  $table1 $query";
            //echo "$sql </br>";
            $model->relationData = $model->fetch($sql); 
        }

        $requestedCoulmns = $model->getRequestedColumns();

        foreach ($model->relationData as &$result) { 
            $id = $result[$primaryKey];
            $sql = "SELECT $requestedCoulmns 
            FROM $table2 
            WHERE $table2.$foreignKey = '$id'" ;
           // echo "$sql </br>";
            $result[$model->relationName] = $model->fetch($sql) ?? [];
        }
    }

    public static function nested ($relation1, $relation2, $primaryKey, $foreignKey) 
    {
        $model = App::$app->model;
        $columns = $model->getRequestedColumns();

        foreach ($model->relationData as &$items) // $users as user
        {
            $items = &$items[$relation1]; // user['posts]
            if(array_key_exists($primaryKey, $items)){
                $id = $items[$primaryKey];//post['id']
                $sql = "SELECT $columns FROM $relation2 WHERE $foreignKey = '$id'";
                $items[$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
            }else{
                foreach ($items as &$item) //posts as post
                {
                    $id = $item[$primaryKey];//post['id']
                    $sql = "SELECT $columns FROM $relation2 WHERE $foreignKey = '$id'";
                    $item[$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
                }
            }
        }
    }
}