<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;
class HasMany extends RelationQueryBuilder {
    public function run (): void 
    {
        $model = App::$app->model;
        $sql = $this->prpareSQL();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);
        $this->inject_data($data);
        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    public function nested (): void 
    {
        $model = App::$app->model;
        $sql = $this->prpareSQL_nested();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);($sql);
        $this->inject_data_nested($data);
        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    private function prpareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $foreignKey = $currentRelation->foreignKey;
        $primaryKey = $currentRelation->primaryKey;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect($table2);
        $query = $this->query->getQuery($table2);
       
        return "SELECT $select FROM $table1
        INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void 
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;

        $foreignKey = $currentRelation->foreignKey;
        $primaryKey = $currentRelation->primaryKey;

        $i = 0;
        foreach ($model->data as &$item) {
            $item[$currentRelation->name] = [];

            while($i < $data->size && $item[$primaryKey] == $data[$i][$foreignKey]){
                $item[$currentRelation->name][] = $data[$i];
                $i++;
            }
        }
    }

    private function prpareSQL_nested (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $foreignKey = $currentRelation->foreignKey;
        $first_sql_part = $currentRelation->FirstSqlPart;
        $alias_PK = $currentRelation->lastJoin_PK;
        $table2 = $currentRelation->table2;
        
        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $model->query->getSelect($table2);
        $query = $this->query->getQuery($table2);
 
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $table2 ON alias1.$alias_PK = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data_nested (_Array $data): void 
    {
        $model = App::$app->model;
        $primaryKey1 = $model->PrimaryKey;

        $currentRelation = $model->relations->currentRelation;
        $relation1 = $currentRelation->relation1;
        $relation2 = $currentRelation->relation2;
        $alias_PK = $currentRelation->lastJoin_PK;
        
        $i = 0;
        foreach ($model->data as &$unit) {
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