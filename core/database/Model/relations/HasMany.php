<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run (): string 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $table2 = $model->currentRelation->table2;
        $foreignKey = $model->currentRelation->foreignKey;
        $primaryKey = $model->currentRelation->primaryKey;

        if(array_key_exists($model->relationName, $model->relationData[0])) {
            return "SELECT :select FROM $table1 
            INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey";
        }

        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
        $orderBy = $model->orderBy;

        $ids = $model->dataIds;
       
        $sql = "SELECT $select FROM $table1 INNER JOIN $table2 
        ON $table1.$primaryKey = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) 
        $query $orderBy";
        // echo "$sql <br>"; 
        $data =  $model->fetch($sql);
        $dataLength = count($data);

        $i = 0;
        foreach ($model->relationData as &$item) {
            $item[$model->relationName] = [];

            while($i < $dataLength && $item[$primaryKey] == $data[$i][$foreignKey]){
                $item[$model->relationName][] = $data[$i];
                $i++;
            }
        }

        $model->query = [];

        return "SELECT :select FROM $table1 
        INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey";
    }

    public static function nested (): void 
    {
        $model = App::$app->model;
        $relation1 = $model->currentRelation->relation1;
        $relation2 = $model->currentRelation->relation2;
        $foreignKey = $model->currentRelation->foreignKey;
        $primaryKey = $model->currentRelation->primaryKey;
        
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