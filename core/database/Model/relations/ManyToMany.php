<?php 

namespace Core\Database\Model\Relations;

use core\App;
use core\base\_Array;

class ManyToMany extends RelationQueryBuilder 
{

    public function run (): void 
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL();
        // echo "$sql <br>";
        $data = App::$app->db->fetch($sql);
        $this->inject_data($data);
        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    public function nested (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL_nested(); 
        // echo "$sql <br>";
        $data = App::$app->db->fetch($sql);
        $this->inject_data_nested($data);
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
        $primaryKey2 = $currentRelation->primaryKey;
        $pivotTable = $currentRelation->pivotTable;
        $pivotKey = $currentRelation->pivotKey;
        $relatedKey = $currentRelation->relatedKey;
        
        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect('alias1');
        $query = $this->query->getQuery('alias1');

        $currentRelation->FirstSqlPart = 
        "INNER JOIN $pivotTable ON $table1.$primaryKey1 = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias1 ON alias1.$primaryKey2 = $pivotTable.$relatedKey";
        $currentRelation->lastJoin_PK = $primaryKey2;
        $currentRelation->lastJoinTable = 'alias1';
       
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $currentRelation->FirstSqlPart 
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void
    {
        $model = App::$app->model;
        $primaryKey1 = $model->PrimaryKey;

        $i = 0;
        foreach ($model->data as &$item) {
            $item[$model->relations->currentRelation->name] = [];

            while($i < $data->size && $item[$primaryKey1] == $data[$i]['pivot']){
                unset($data[$i]['pivot']);
                $item[$model->relations->currentRelation->name][] = $data[$i];
                $i++;
            }
        }
    }

    private function prepareSQL_nested ()
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $primaryKey2 = $currentRelation->primaryKey;
        $pivotTable = $currentRelation->pivotTable;
        $relatedKey = $currentRelation->relatedKey;
        $pivotKey = $currentRelation->pivotKey;
        $first_sql_part = $currentRelation->FirstSqlPart;
        $lastTable = $currentRelation->lastJoinTable;
        $lastTable_PK = $currentRelation->lastJoin_PK;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect('alias2');
        $query = $this->query->getQuery();
 
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $pivotTable ON $lastTable.$lastTable_PK = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias2 ON alias2.$primaryKey2 = $pivotTable.$relatedKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data_nested (_Array $data): void
    {
        $model = App::$app->model;

        $primaryKey1 = $model->PrimaryKey;
        $current_relation = $model->relations->currentRelation; 
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;
        $primaryKey2 = $current_relation->primaryKey;
 
        $i = 0;
        foreach ($model->data as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($primaryKey2, $unit[$relation1])){
                $unit[$relation1][$relation2] = [];
                while($i < $data->size && $unit[$primaryKey1] == $data[$i]['pivot']){
                    unset($data[$i]['pivot']);
                    $unit[$relation1][$relation2][] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                   $item[$relation2] = [];
                    while($i < $data->size && $unit[$primaryKey1] == $data[$i]['pivot']){
                        unset($data[$i]['pivot']);
                        $item[$relation2][] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    }
}