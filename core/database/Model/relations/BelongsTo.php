<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run (): void 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;

        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $table2 = $model->currentRelation['table2'];
        $foreignKey = $model->currentRelation['foreignKey'];
        $primaryKey = $model->currentRelation['primaryKey'];

        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
        $orderBy = $model->orderBy;

        $ids = $model->dataIds;
       
        $sql = "SELECT $select FROM $table1 LEFT JOIN $table2 
        ON $table2.$primaryKey = $table1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
        //echo "$sql <br>"; 
        $data = $model->fetch($sql);

        foreach ($model->relationData as $key => &$item) {
            $item[$model->relationName] = $data[$key];
        }

        $model->query = [];
    }

    public static function nested (): void
    {
        $model = App::$app->model;
        $relation1 = $model->currentRelation['relation1'];
        $relation2 = $model->currentRelation['relation2'];
        $table2 = $model->currentRelation['table2'];
        $primaryKey = $model->currentRelation['primaryKey'];
        $foreignKey = $model->currentRelation['foreignKey'];

        $extraQuery = $model->extraQuery();
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        foreach ($model->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($foreignKey, $unit[$relation1])){
                $id = $unit[$relation1][$foreignKey];
                $sql = "SELECT $select FROM $table2 WHERE $primaryKey = '$id' $query";
                //echo "$sql <br>";
                $unit[$relation1][$relation2] = $model->fetch($sql)[0];
            }else {
                foreach ($unit[$relation1] as &$item) {
                    $id = $item[$foreignKey]; 
                    $sql = "SELECT $select FROM $table2 WHERE $primaryKey = '$id' $query";
                    //echo "$sql <br>";
                    $item[$relation2] = $model->fetch($sql)[0];
                }
            }
        }
    }
}