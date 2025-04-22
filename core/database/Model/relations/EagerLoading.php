<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;
use core\base\_Srting;

class EagerLoading
{
    public function handleWith(string $class): _Array
    {
        $class = new $class();
        $model = App::$app->model;
       
        foreach ($model->relations->relations as $relation) { 
            $relation = new _Srting($relation);
            if($relation->contains(':'))
            {
                $this->getRequestedColumns($relation);
                $relation->set($model->relations->currentRelation->name);
            }

            $model->relations->Nested->run($class::class, $relation);
        }

        return $model->data;
    }

    private function getRequestedColumns (_Srting $relation): void 
    {
        $model = App::$app->model;
        $colonPostion = $relation->position(':');
        $model->relations->currentRelation->name = $relation->subString(0, $colonPostion);
        $model->relations->currentRelation->columns = $relation->subString($colonPostion + 1);
    } 
    
    public function handleWithCount (): void 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;

        foreach ($model->relations->withCount_relations as $relationName) {
            $forigenKey = App::$app->db->getFK($relationName, $table1);

            $sql = "SELECT COUNT(*) AS count, $table1.$primaryKey AS pivot FROM $table1 
            INNER JOIN $relationName ON $table1.$primaryKey = $relationName.$forigenKey
            WHERE $table1.$primaryKey IN ($ids)
            GROUP BY $table1.$primaryKey $orderBy";

            //echo "$sql <br> <br>";

            $data = App::$app->db->fetch($sql);

            $i = 0;
            foreach ($model->data as &$item) {
                $item[$relationName.'Count'] = 0;

                if($i < $data->size && $item[$primaryKey] === $data[$i]['pivot'] ){
                    $item[$relationName.'Count'] = $data[$i]['count'];
                    $i++;
                }               
            }
        }
    }
}