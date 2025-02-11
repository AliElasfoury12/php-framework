<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run (): void 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;
        $requestedCoulmns = $model->getRequestedColumns();
        $table2 = $model->currentRelation['table2'];
        $foreignKey = $model->currentRelation['foreignKey'];
        $primaryKey = $model->currentRelation['primaryKey'];

        foreach ($model->relationData as &$result) { 
            $id = $result[$primaryKey];
            $sql = "SELECT $requestedCoulmns 
            FROM $table2 
            WHERE $foreignKey = '$id'" ;
            //echo "$sql </br>";
            $result[$model->relationName] = $model->fetch($sql) ?? [];
        }
    }

    public static function nested (): void 
    {
        $model = App::$app->model;
        $relation1 = $model->currentRelation['relation1'];
        $relation2 = $model->currentRelation['relation2'];
        $columns = isset($model->requestedCoulmns["$relation1.$relation2"])?: '*';
        $primaryKey = $model->currentRelation['primaryKey'];
        $foreignKey = $model->currentRelation['foreignKey'];

        foreach ($model->relationData as &$items) // $users as user
        {
            if(empty($items[$relation1])) continue;
            
            if(array_key_exists($primaryKey, $items)){
                $id = $items[$relation1][$primaryKey];//post['id']
                $sql = "SELECT $columns FROM $relation2 WHERE $foreignKey = '$id'";
                $items[$relation1][$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
            }else{
                foreach ($items as &$item) //posts as post
                {
                    $id = $item[$primaryKey];//post['id']
                    $sql = "SELECT $columns FROM $relation2 WHERE $foreignKey = '$id'";
                    $item[$relation1][$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
                }
            }
        }
    }
}