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

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::BELONGSTO;
        $model->relations->commonData($class1, $class2);

        $table1 = $currentRelation->table1;
        $table2 = $currentRelation->table2;
        
        $currentRelation->FK1 = $foreignKey ?: $db->getFK($table1, $table2);
        $currentRelation->PK2 = $primaryKey?: $db->getPK($table1);
       
        return $this;
    }
}