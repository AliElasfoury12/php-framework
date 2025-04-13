<?php 

namespace core\database\Model\relations;

use core\App;

class EagerLoading
{
    public function handleWith(array $relations, string $class): array
    {
        $class = new $class();
        $model = App::$app->model;
        foreach ($relations as $relation) { 

            if(str_contains($relation, ':')) //posts:id,post
            {
                $this->getRequestedColumns($relation);
                $relation = $model->relations->relationName;
            }

            if(str_contains($relation, '.')) //posts.comments
            {
                $model->relations->Nested->run($class::class, $relation);
                $model->relations->requestedCoulmns = '';
                continue;
            }

            $model->select($model->relations->requestedCoulmns);
            call_user_func([$class, $model->relations->relationName]);
            $model->relations->handleRelation();
        }

        return $model->relations->relationData;
    }

    private function getRequestedColumns (string $relation): void 
    {
        $model = App::$app->model;
        $colonPostion = strpos($relation,':');
        $model->relations->relationName = substr($relation, 0, $colonPostion);
        $model->relations->requestedCoulmns = substr($relation, $colonPostion + 1);
    } 
    
    public function handleWithCount (): void 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        foreach ($model->relations->withCount_relations as $relationName) {
            $forigenKey = App::$app->db->getFK($relationName, $table1);

            $sql = "SELECT COUNT(*) AS count, $table1.$primaryKey AS pivot FROM $table1 
            INNER JOIN $relationName ON $table1.$primaryKey = $relationName.$forigenKey
            WHERE $table1.$primaryKey IN ($ids)
            GROUP BY $table1.$primaryKey $orderBy";

            //echo "$sql <br> <br>";

            $data = App::$app->db->query($sql);
            $data_length = count($data);

            $i = 0;
            foreach ($model->relations->relationData as &$item) {
                $item[$relationName.'Count'] = 0;

                if($i < $data_length && $item[$primaryKey] === $data[$i]['pivot'] ){
                    $item[$relationName.'Count'] = $data[$i]['count'];
                    $i++;
                }               
            }
        }
    }
}