<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo extends Relations {
    
    public static function run (): void
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;

        $sql = self::prepareSQL();
        //echo "$sql <br>"; 
        $data = $model->fetch($sql);

        foreach ($model->relationData as $key => &$item) {
            $item[$model->relationName] = $data[$key];
        }

        $model->query = [];
    }

    private static function prepareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;

        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        $current_relation->FirstSqlPart = 
        "INNER JOIN $table2  ON $table2.$primaryKey2 = $table1.$foreignKey";
        $current_relation->lastJoin_PK = $primaryKey2;
        $current_relation->lastJoinTable = $table2;
      
        return "SELECT $select FROM $table1 
        INNER JOIN $table2 ON $table2.$primaryKey2 = $table1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    public static function nested (): void
    {
        $model = App::$app->model;
        $current_relation = $model->currentRelation;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;

        $sql = self::prepareSQL_nested();
        //echo "$sql <br>";
        
        $data = $model->fetch($sql);
        $data_length = count($data);

        self::inject_data($relation1,$relation2,$data,$data_length);
      
    }

    private static function prepareSQL_nested (): string
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $ids = $model->dataIds;
        $orderBy = $model->orderBy;

        $current_relation = $model->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;
        $first_sql_part = $current_relation->FirstSqlPart;

        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        return "SELECT $select FROM $table1 
        $first_sql_part
        INNER JOIN $table2 ON $table2.$primaryKey2 = alias.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private static function inject_data (string $relation1, string $relation2, array $data, int $data_length): void
    {
        $model = App::$app->model;
        $current_relation = $model->currentRelation;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;

        $i = 0;
        foreach ($model->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;
            if(array_key_exists($foreignKey, $unit[$relation1])){
                if($i < $data_length && $unit[$relation1][$foreignKey] == $data[$i][$primaryKey2]){
                    $unit[$relation1][$relation2] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                    if($i < $data_length  && $item[$foreignKey] == $data[$i][$primaryKey2]){
                        $item[$relation2] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    } 
}