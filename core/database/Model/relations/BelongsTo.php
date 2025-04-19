<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;

class BelongsTo extends RelationQueryBuilder
{

    public function run (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);
        foreach ($model->data as $key => &$item) {
            $item[$model->relations->currentRelation->name] = $data[$key];
        }

        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    public function nested (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL_nested();
        //echo "$sql <br>";
        $data = App::$app->db->fetch($sql);
        $this->inject_data($data);
        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    private function prepareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $foreignKey = $currentRelation->foreignKey;
        $primaryKey2 = $currentRelation->primaryKey;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect($table2);
        $query = $this->query->getQuery($table2);

        $currentRelation->FirstSqlPart = 
        "INNER JOIN $table2  ON $table2.$primaryKey2 = $table1.$foreignKey";
        $currentRelation->lastJoin_PK = $primaryKey2;
        $currentRelation->lastJoinTable = $table2;
      
        return "SELECT $select FROM $table1 
        INNER JOIN $table2 ON $table2.$primaryKey2 = $table1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function prepareSQL_nested (): string
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $ids = $model->ids;
        $orderBy = $model->orderBy;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $foreignKey = $currentRelation->foreignKey;
        $primaryKey2 = $currentRelation->primaryKey;
        $first_sql_part = $currentRelation->FirstSqlPart;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect($table2);
        $query = $this->query->getQuery($table2);

        return "SELECT $select FROM $table1 
        $first_sql_part
        INNER JOIN $table2 ON $table2.$primaryKey2 = alias1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        
        $foreignKey = $currentRelation->foreignKey;
        $primaryKey2 = $currentRelation->primaryKey;
        $relation1 = $currentRelation->relation1;
        $relation2 = $currentRelation->relation2;

        $i = 0;
        foreach ($model->data as &$unit) {
            if(!$unit[$relation1]) continue;
            if(@$unit[$relation1][$foreignKey]){
                if($i < $data->size && $unit[$relation1][$foreignKey] == $data[$i][$primaryKey2]){
                    $unit[$relation1][$relation2] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                    if($i < $data->size  && $item[$foreignKey] == $data[$i][$primaryKey2]){
                        $item[$relation2] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    } 
}