<?php 

namespace core\database\Model\Relations;

use core\App;
use core\base\_Array;

class BelongsTo extends RelationQueryBuilder
{
    public function makeRelation (string $class1, string $class2, string $foreignKey, string $primaryKey):BelongsTo
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $table1 = $model->getClassTable($class1);
        $table2 = $model->getClassTable($class2);

        $primaryKey = $primaryKey?: $db->getPK($table1);
        $foreignKey = $foreignKey ?: $db->getFK($table1, $table2);
       
        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::BELONGSTO;
        $currentRelation->table1 = $table1;
        $currentRelation->table2 = $table2;
        $currentRelation->FK1 = $foreignKey;
        $currentRelation->PK2 = $primaryKey;
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;

        return $this;
    }
}