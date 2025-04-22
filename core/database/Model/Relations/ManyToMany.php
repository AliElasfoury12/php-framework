<?php 

namespace core\database\Model\Relations;

use core\App;
use core\base\_Array;

class ManyToMany extends RelationQueryBuilder 
{
    public function makeRelation (string $baseClass, string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): static
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::MANYTOMANY;
        $currentRelation->table1 = $model->getClassTable($baseClass);
        $currentRelation->table2 = $model->getClassTable($relatedClass);
        $currentRelation->PK1 = $db->getPK($currentRelation->table1);
        $currentRelation->PK2 = $db->getPK($currentRelation->table2);
        $currentRelation->pivotTable = $pivotTable;
        $currentRelation->pivotKey = $pivotKey;
        $currentRelation->relatedKey = $relatedKey;
        $currentRelation->model1 = $baseClass;
        $currentRelation->model2 = $relatedClass;

        return $this;
    }
}