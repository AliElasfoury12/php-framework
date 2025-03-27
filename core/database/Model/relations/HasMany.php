<?php 

namespace core\database\Model\relations;

use core\App;
class HasMany extends Relations {
    public static function run (): void 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;

        $sql = self::prpareSQL();
        // echo "$sql <br>"; 
        $data = $model->fetch($sql);
        
        self::inject_data($data);
        $model->query = [];
    }

    private static function prpareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $table2 = $model->currentRelation->table2;
        $foreignKey = $model->currentRelation->foreignKey;
        $primaryKey = $model->currentRelation->primaryKey;

        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
       
       
        return "SELECT $select FROM $table1
        INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private static function inject_data (array $data): void 
    {
        $model = App::$app->model;
        $foreignKey = $model->currentRelation->foreignKey;
        $primaryKey = $model->currentRelation->primaryKey;
        $data_length = count($data);

        $i = 0;
        foreach ($model->relationData as &$item) {
            $item[$model->relationName] = [];

            while($i < $data_length && $item[$primaryKey] == $data[$i][$foreignKey]){
                $item[$model->relationName][] = $data[$i];
                $i++;
            }
        }

    }

    public static function nested (): void 
    {
        $model = App::$app->model;
        $sql = self::prpareSQL_nested();
        //echo "$sql <br>"; 
        $data = $model->fetch($sql);
        self::inject_data_nested($data);
        $model->query = [];
    }

    private static function prpareSQL_nested (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->currentRelation;
        $foreignKey = $current_relation->foreignKey;
        $first_sql_part = $current_relation->FirstSqlPart;
        $alias_PK = $current_relation->lastJoin_PK;
        $table2 = $current_relation->table2;
        
        $extraQuery = $model->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
 
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $table2 ON alias1.$alias_PK = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private static function inject_data_nested (array $data): void 
    {
        $model = App::$app->model;
        $primaryKey1 = $model->primaryKey;

        $current_relation = $model->currentRelation;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;
        $alias_PK = $current_relation->lastJoin_PK;

        $data_length = count($data);
        
        $i = 0;
        foreach ($model->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($alias_PK, $unit[$relation1])){
                $unit[$relation1][$relation2] = [];
                while($i < $data_length && $unit[$primaryKey1] == $data[$i]['pivot']){
                    unset($data[$i] ['pivot']);
                    $unit[$relation1][$relation2][] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                   $item[$relation2] = [];
                    while($i < $data_length  && $unit[$primaryKey1] == $data[$i]['pivot']){
                        unset($data[$i] ['pivot']);
                        $item[$relation2][] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    }


        
}