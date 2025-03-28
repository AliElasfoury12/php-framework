<?php 

namespace core\database\Model\relations;

use core\App;

class BelongsTo {
    
    public function run (): void
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;

        $sql = $this->prepareSQL();
        //echo "$sql <br>"; 
        $data = $model->fetch($sql);

        foreach ($model->relations->relationData as $key => &$item) {
            $item[$model->relations->relationName] = $data[$key];
        }

        $model->query->reset();
    }

    private function prepareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;

        $extraQuery = $model->relations->extraQuery($table2);
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

    public function nested (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL_nested();
        //echo "$sql <br>";
        
        $data = $model->fetch($sql);
        $this->inject_data($data);
        $model->query->reset();
    }

    private function prepareSQL_nested (): string
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $ids = $model->dataIds;
        $orderBy = $model->orderBy;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;
        $first_sql_part = $current_relation->FirstSqlPart;

        $extraQuery = $model->relations->extraQuery($table2);
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        return "SELECT $select FROM $table1 
        $first_sql_part
        INNER JOIN $table2 ON $table2.$primaryKey2 = alias1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (array $data): void
    {
        $model = App::$app->model;
        $data_length = count($data);
        $current_relation = $model->relations->currentRelation;
        
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;

        $i = 0;
        foreach ($model->relations->relationData as &$unit) {
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