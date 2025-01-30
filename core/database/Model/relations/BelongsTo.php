<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run ($table1, $table2, $foreignKey, $primaryKey): array 
    {
        //table1 posts belongsTO table2 users
        $model = &App::$app->model;

        if($model->relationData){
            $result1 = $model->relationData; 
        }else {
            $result1 = self::fetchBelongsToTable(
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


        for ($i=0; $i < count($result1); $i++) { 
            $result1[$i][$model->relationName] = $result2[$i];
        }

        $model->query = [];
        return $result1;
    }

    private static function fetchBelongsToTable ($table, $table1, $table2, $primryKey, $foreignKey) 
    {
        $query = '';
        $model = &App::$app->model;
    
       if($table == $table2) $requestedCoulmns = $model->getRequestedColumns($table);

        if($table == $table1 ) {
            $query = $model->getQuery($table1);
            if(array_key_exists('select', $model->query)) {
                $requestedCoulmns = $model->handleSelect($table);
            }
        }

        $sql = "SELECT $requestedCoulmns FROM 
        $table1 LEFT JOIN $table2 
        on $table2.$primryKey = $table1.$foreignKey $query"; //users.id = posts.user_id
        //echo $sql;
       
        return $model->fetch($sql);
    }
}