<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run ($table1, $table2, $foreignKey , $primaryKey ) :array 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;

        if($model->relationData) {
            $result1 = $model->relationData;
        }else {
            $query = $model->getQuery();
            $select = $model->handleSelect();
            $sql = "SELECT $select FROM  $table1 $query";
            //echo "$sql </br>";
            $result1 = $model->fetch($sql); 
        }

        $requestedCoulmns = $model->getRequestedColumns();

        foreach ($result1 as &$result) { 
            $result1Id = $result[$primaryKey];
            $sql = "SELECT $requestedCoulmns 
            FROM $table2 
            WHERE $table2.$foreignKey = '$result1Id'" ;
           // echo "$sql </br>";
            $result2 = $model->fetch($sql);
            $result[$model->relationName] = $result2 ?? [];
        }

        $model->query = [];
        return $result1;
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