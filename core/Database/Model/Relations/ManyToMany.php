<?php 

namespace core\Database\Model\Relations;

use core\App;

class ManyToMany extends RelationQueryBuilder 
{
    public function makeRelation (string $class1, string $class2, string $pivotTable, string $pivotKey, string $relatedKey): static
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $model->relations->commonData($class1, $class2);

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::MANYTOMANY;
        $currentRelation->pivotTable = $pivotTable;
        $currentRelation->pivotKey = $pivotKey;
        $currentRelation->relatedKey = $relatedKey;
        $currentRelation->PK1 = $db->getPK($currentRelation->table1);
        $currentRelation->PK2 = $db->getPK($currentRelation->table2);

        return $this;
    }
}