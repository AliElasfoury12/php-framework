<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run ($table1, $table2, $foreignKey, $primaryKey): void 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;

        if(!$model->relationData){
            $model->relationData = self::fetchBelongsToTable(
                $table1,
                $table1,
                $table2,
                $primaryKey,
                $foreignKey); //posts
        }

        $result2 = self::fetchBelongsToTable(
            $table2,
            $table1,
            $table2,
            $primaryKey,
            $foreignKey); //users

        $length = count($model->relationData);

        for ($i=0; $i < $length; $i++) { 
            $model->relationData[$i][$model->relationName] = $result2[$i];
        }

        $model->query = [];
    }

    private static function fetchBelongsToTable ($table, $table1, $table2, $primryKey, $foreignKey) 
    {
        $query = '';
        $model = App::$app->model;
    
       if($table == $table2) $requestedCoulmns = $model->getRequestedColumns($table);

        if($table == $table1 ) {
            $query = $model->getQuery($table1);
            if($model->query['select']) {
                $requestedCoulmns = $model->handleSelect($table);
            }
        }

        $sql = "SELECT $requestedCoulmns FROM 
        $table1 LEFT JOIN $table2 
        on $table2.$primryKey = $table1.$foreignKey $query"; //users.id = posts.user_id
        //echo $sql;
       
        return $model->fetch($sql);
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