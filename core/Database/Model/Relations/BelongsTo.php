<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\base\OrderSet;
use core\Database\Model\MainModel;

class BelongsTo 
{
    public function createRelation (MainModel $model1, string $class2, string $foreignKey, string $primaryKey): MainModel  
    {
        $relation = $model1->RelationsClass->sigenCommonRelationData($model1,$class2);
        $model2 = $relation->model;
        $relation->type = RELATIONSTYPE::BELONGSTO;
        $this->getRelationKeys($primaryKey, $foreignKey, $model1, $model2);
        return $model2;
    }

    private function getRelationKeys (string $primaryKey, string $foreignKey,MainModel $model1,MainModel $model2): void 
    {
        if($foreignKey) $model1->foreignKeys[$model2->table] = $foreignKey;
        else $model1->getRelatedForigenKey($model2);

        if($primaryKey) $model2->primaryKey = $primaryKey;
        else  $model2->getPrimaryKey();
    }
    public function handleRelation (MainModel $model1, Relation $relation): void 
    {
        $model2 = $relation->model;
        $this->getBelongsToData($model1,$model2);
        if(!$model2->relations->empty())  $model2->handleRelations(false);
        $this->injectBelongsToData($model1,$model2,$relation);
    }

    private function getBelongsToData (MainModel $model1, MainModel $model2): void 
    {
        $foreignKey = $model1->foreignKeys[$model2->table];
        $foreignKeys = $this->getForeignKeys($model1,$foreignKey);

        $sql = $this->createSql($model2, $foreignKeys);
        $model2->data = App::$app->db->fetch($sql);
    }

    private function createSql (MainModel $model2, string $foreignKeys): string 
    {
        $select = $model2->query->getSelect();
        $model2->where($model2->primaryKey,'IN',$foreignKeys);
        $query = $model2->query->getQuery();

        return "SELECT $select FROM {$model2->table} $query";
    }

    private function getForeignKeys (MainModel $model1, string $foreignKey): string 
    {
        $orderSet = new OrderSet;
        foreach ($model1->data as  $value) {
            $orderSet->add($value[$foreignKey]);
        }
        return $orderSet->join();
    }

    private function injectBelongsToData (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $foreigenKey = $model1->foreignKeys[$model2->table];
        $relationName = $relation->name;

        $helper = [];
        foreach ($model2->data as  $value) {
            $helper[$value[$model2->primaryKey]] = $value;
        }

        $model2->data->reset();

        foreach ($model1->data as  &$value) {
            $value[$relationName] = $helper[$value[$foreigenKey]] ?? [];
        }
    }
}