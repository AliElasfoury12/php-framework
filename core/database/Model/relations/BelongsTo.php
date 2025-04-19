<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;
use core\database\Model\Query;
use core\database\Model\QueryBuilder;

class BelongsTo 
{
    public Query $query;
    public function __construct()
    {
        $this->query = new Query;
    }

    public function select (string $columns)
    {
        $this->query->select = $columns;
    }
    
    public function run (): void
    {
        $model = App::$app->model;

        $sql = $this->prepareSQL();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);

        foreach ($model->relations->RelationsData as $key => &$item) {
            $item[$model->relations->currentRelation->name] = $data[$key];
        }

        $model->query->reset();
    }

    private function prepareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;

        $select = $this->query->getSelect($table2);
        $query = $model->query->getQuery($table2);

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
        
        $data = App::$app->db->fetch($sql);
        $this->inject_data($data);
        $model->query->reset();
    }

    private function prepareSQL_nested (): string
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $ids = $model->ids;
        $orderBy = $model->orderBy;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;
        $first_sql_part = $current_relation->FirstSqlPart;

        $select = $model->query->getSelect($table2);
        $query = $model->query->getQuery($table2);

        return "SELECT $select FROM $table1 
        $first_sql_part
        INNER JOIN $table2 ON $table2.$primaryKey2 = alias1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void
    {
        $model = App::$app->model;
        $current_relation = $model->relations->currentRelation;
        
        $foreignKey = $current_relation->foreignKey;
        $primaryKey2 = $current_relation->primaryKey;
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;

        $i = 0;
        foreach ($model->relations->RelationsData as &$unit) {
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