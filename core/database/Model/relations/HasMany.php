<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run (): void 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;
        $table2 = $model->currentRelation['table2'];
        $foreignKey = $model->currentRelation['foreignKey'];
        $primaryKey = $model->currentRelation['primaryKey'];

        $extraQuery = $model->extraQuery();
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        foreach ($model->relationData as &$result) { 
            $id = $result[$primaryKey];
            $sql = "SELECT $select 
            FROM $table2 
            WHERE $foreignKey = '$id' $query";
            //echo "$sql </br>";
            $result[$model->relationName] = $model->fetch($sql) ?? [];
        }

        $model->query = [];
    }

    public static function nested (): void 
    {
        $model = App::$app->model;
        $relation1 = $model->currentRelation['relation1'];
        $relation2 = $model->currentRelation['relation2'];
        $primaryKey = $model->currentRelation['primaryKey'];
        $foreignKey = $model->currentRelation['foreignKey'];
        
        $extraQuery = $model->extraQuery();
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        foreach ($model->relationData as &$items) // $users as user
        {
            if(empty($items[$relation1])) continue;
            
            if(array_key_exists($primaryKey, $items)){
                $id = $items[$relation1][$primaryKey];//post['id']
                $sql = "SELECT $select FROM $relation2 WHERE $foreignKey = '$id' $query";
                //echo "$sql </br>";
                $items[$relation1][$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
            }else{
                foreach ($items as &$item) //posts as post
                {
                    $id = $item[$primaryKey];//post['id']
                    $sql = "SELECT $select FROM $relation2 WHERE $foreignKey = '$id' $query";
                    //echo "$sql </br>";
                    $item[$relation1][$relation2] = $model->fetch($sql); //comments  $post[comments] = nested
                }
            }
        }
    }
}