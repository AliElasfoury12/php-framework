<?php 

namespace core\Database\Model\Relations;

use core\App;

class HasMany extends RelationQueryBuilder {
    public function makeRelation (string $class1, string $class2, string $foreignKey, string $primaryKey):HasMany
    {
        $model = App::$app->model;
        $db = App::$app->db;
        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::HASMANY;

        $model->relations->commonData($class1, $class2);

        $table1 = $currentRelation->table1;
        $table2 = $currentRelation->table2;
        $currentRelation->PK1 = $primaryKey?: $db->getPK($table1);
        $currentRelation->FK2 = $foreignKey ?: $db->getFK($table2, $table1);
        
        return $this;
    }
}