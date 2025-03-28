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
                $model->requestedCoulmns = '';
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
        $primaryKey = $model->primaryKey;

        foreach ($model->relations->withCount_relations as $relationName) {
            $forigenKey =  App::$app->db->getFK($relationName, $model->table);

            foreach ($model->relations->relationData as &$item) {
                $id = $item[$primaryKey];
                $sql = "SELECT COUNT(*) FROM $relationName WHERE $forigenKey = '$id'";
                $count =  App::$app->db->query($sql)[0]['COUNT(*)'];
                $item[$relationName.'Count'] = $count ?? 0;
            }
        }
    }
}