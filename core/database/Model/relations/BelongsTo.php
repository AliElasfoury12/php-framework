<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run (): void 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $requestedCoulmns = $model->getRequestedColumns();

        $table2 = $model->currentRelation['table2'];
        $foreignKey = $model->currentRelation['foreignKey'];
        $primaryKey = $model->currentRelation['primaryKey'];

        foreach ($model->relationData as &$item) { 
            $id = $item[$foreignKey];
            $sql = "SELECT $requestedCoulmns 
            FROM $table2 
            WHERE $primaryKey = '$id'" ;
            //echo "$sql </br>";
            $item[$model->relationName] = $model->fetch($sql)[0] ?? [];
        }
    }

    public static function nested (): void
    {
        $model = App::$app->model;
        $relation1 = $model->currentRelation['relation1'];
        $relation2 = $model->currentRelation['relation2'];
        $table2 = $model->currentRelation['table2'];
        $columns = isset($model->requestedCoulmns["$relation1.$relation2"])?: '*';
        $primaryKey = $model->currentRelation['primaryKey'];
        $foreignKey = $model->currentRelation['foreignKey'];

        foreach ($model->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($foreignKey, $unit[$relation1])){
                $id = $unit[$relation1][$foreignKey];
                $sql = "SELECT $columns FROM $table2 WHERE $primaryKey = '$id'";
                //echo "$sql <br>";
                $unit[$relation1][$relation2] = $model->fetch($sql)[0];
            }else {
                foreach ($unit[$relation1] as &$item) {
                    $id = $item[$foreignKey]; 
                    $sql = "SELECT $columns FROM $table2 WHERE $primaryKey = '$id'";
                    //echo "$sql <br>";
                    $item[$relation2] = $model->fetch($sql)[0];
                }
            }
        }
    }
}