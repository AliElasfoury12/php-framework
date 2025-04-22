<?php 

namespace core\database\Model\Relations;

use core\App;

class HasMany extends RelationQueryBuilder {
    public function makeRelation (string $class1, string $class2, string $foreignKey = '', string $primaryKey = ''):HasMany
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $db = App::$app->db;

        $table1 = $model->getClassTable($class1);
        $table2 = $model->getClassTable($class2);

        $primaryKey = $primaryKey?: $db->getPK($table1);
        $foreignKey = $foreignKey ?: $db->getFK($table2, $table1) ;

        $currentRelation->type = $model->relations->Types::HASMANY;
        $currentRelation->table1 = $table1;
        $currentRelation->table2 = $table2;
        $currentRelation->FK2 = $foreignKey;
        $currentRelation->PK1 = $primaryKey;
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;
        return $this;
    }
}