<?php 

namespace core\database\Model\relations;

use core\App;

trait WithCountTrait
{
    public function handleWithCount (): void 
    {
        $model = App::$app->model;
        $primaryKey = $model->primaryKey;

        foreach ($model->relations->withCount_relations as $relationName) {
            $forigenKey = $model->relations->getFK($relationName, $model->table);

            foreach ($model->relations->relationData as &$item) {
                $id = $item[$primaryKey];
                $sql = "SELECT COUNT(*) FROM $relationName WHERE $forigenKey = '$id'";
                $count =  $model->fetch($sql)[0]['COUNT(*)'];
                $item[$relationName.'Count'] = $count ?? 0;
            }
        }
    }
}
