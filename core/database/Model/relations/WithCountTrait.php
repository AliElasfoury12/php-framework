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
