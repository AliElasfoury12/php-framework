<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run ($table1, $table2, $foreignKey, $primaryKey): void 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $requestedCoulmns = $model->getRequestedColumns();

        foreach ($model->relationData as &$item) { 
            $id = $item[$foreignKey];
            $sql = "SELECT $requestedCoulmns 
            FROM $table2 
            WHERE $primaryKey = '$id'" ;
            //echo "$sql </br>";
            $item[$model->relationName] = $model->fetch($sql)[0] ?? [];
        }
    }

    public static function nested ($relation1, $relation2, $table2, $primaryKey, $foreignKey)
    {
        $model = App::$app->model;
        $coulmns = $model->requestedCoulmns["$relation1.$relation2"] ?? '*';

        foreach ($model->relationData as &$unit) {
            if(empty( $unit[$relation1])) continue;

            if(array_key_exists($foreignKey, $unit[$relation1])){
                $id = $unit[$relation1][$foreignKey];
                $sql = "SELECT $coulmns FROM $table2 WHERE $primaryKey = '$id'";
                //echo "$sql <br>";
                $unit[$relation1][$relation2] = $model->fetch($sql)[0];
            }else {
                foreach ($unit[$relation1] as &$item) {
                    $id = $item[$foreignKey]; 
                    $sql = "SELECT $coulmns FROM $table2 WHERE $primaryKey = '$id'";
                    //echo "$sql <br>";
                    $item[$relation2] = $model->fetch($sql)[0];
                }
            }
        }
    }
}