<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;
class HasMany {
    public function run (): void 
    {
        //table1 users hasMany table2 posts
        $model = App::$app->model;

        $sql = $this->prpareSQL();
        // echo "$sql <br>"; 
        $data =App::$app->db->fetch($sql);($sql);

        $this->inject_data($data);
        $model->query->reset();
    }

    private function prpareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;

        $table2 = $model->relations->currentRelation->table2;
        $foreignKey = $model->relations->currentRelation->foreignKey;
        $primaryKey = $model->relations->currentRelation->primaryKey;

        $select = $model->query->getSelect($table2);
        $query = $model->query->getQuery($table2);
       
        return "SELECT $select FROM $table1
        INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void 
    {
        $model = App::$app->model;
        $foreignKey = $model->relations->currentRelation->foreignKey;
        $primaryKey = $model->relations->currentRelation->primaryKey;

        $i = 0;
        foreach ($model->relations->RelationsData as &$item) {
            $item[$model->relations->currentRelation->name] = [];

            while($i < $data->size && $item[$primaryKey] == $data[$i][$foreignKey]){
                $item[$model->relations->currentRelation->name][] = $data[$i];
                $i++;
            }
        }
    }

    public function nested (): void 
    {
        $model = App::$app->model;
        $sql = $this->prpareSQL_nested();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);($sql);
        $this->inject_data_nested($data);
        $model->query->reset();
    }

    private function prpareSQL_nested (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;

        $current_relation = $model->relations->currentRelation;
        $foreignKey = $current_relation->foreignKey;
        $first_sql_part = $current_relation->FirstSqlPart;
        $alias_PK = $current_relation->lastJoin_PK;
        $table2 = $current_relation->table2;
        
        $select = $model->query->getSelect($table2);
        $query = $model->query->getQuery($table2);
 
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $table2 ON alias1.$alias_PK = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data_nested (_Array $data): void 
    {
        $model = App::$app->model;
        $primaryKey1 = $model->PrimaryKey;

        $current_relation = $model->relations->currentRelation;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;
        $alias_PK = $current_relation->lastJoin_PK;
        
        $i = 0;
        foreach ($model->relations->RelationsData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($alias_PK, $unit[$relation1])){
                $unit[$relation1][$relation2] = [];
                while($i < $data->size && $unit[$primaryKey1] == $data[$i]['pivot']){
                    unset($data[$i] ['pivot']);
                    $unit[$relation1][$relation2][] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                   $item[$relation2] = [];
                    while($i < $data->size  && $unit[$primaryKey1] == $data[$i]['pivot']){
                        unset($data[$i] ['pivot']);
                        $item[$relation2][] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    }
}