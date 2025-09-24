<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\base\_Array;
use core\Database\Model\MainModel;

class HasMany  {

    public function createRelation (MainModel $model1, string $class2, string $foreignKey, string $primaryKey): MainModel  
    {
        $relation = $model1->RelationsClass->signCommonRelationData($model1,$class2);
        $model2 = $relation->model;
        $relation->type = RELATIONSTYPE::HASMANY;
        $this->getRelationKeys($primaryKey,$foreignKey,$model1,$model2);

        return $model2;
    }

    private function getRelationKeys (string $primaryKey,string $foreignKey,MainModel $model1,MainModel $model2): void 
    {
        if($foreignKey) $model2->foreignKeys[$model1->table] = $foreignKey;
        else $model2->getRelatedForigenKey($model1);

        if($primaryKey) $model1->primaryKey = $primaryKey;
        else  $model1->getPrimaryKey();
    }
    public function handleRelation (MainModel $model1, Relation $relation): void 
    {
        $model2 = $relation->model;
        $this->getHasManyData($model1,$relation);
        if(!$model2->relations->empty()) $model2->handleRelations(false);
        if($relation->isWithCount) $this->injectHasManyCountData($model1, $relation);
        else $this->injectHasManyData($model1,$relation);
    }

    private function getHasManyData (MainModel $model1, Relation $relation): void 
    {
        $model2 = $relation->model;
        if(!$model1->ids) $model1->getActiveIds();
        $sql = $this->createSql($model1,$relation);
        $model2->data = App::$app->db->fetch($sql);
    }

    private function createSql (MainModel $model1,Relation $relation): string 
    {
        $model2 = $relation->model;
        $foreignKey = $model2->getRelatedForigenKey($model1);
        if($relation->isWithCount){
            $select = "COUNT(*) AS count, $foreignKey";
            $model2->groupBy($foreignKey);
        }else  $select = $model2->query->getSelect();

        $model2->where($foreignKey,'IN',$model1->ids);
        $query = $model2->query->getQuery();
        return "SELECT $select FROM {$model2->table} $query";
    }

    private function injectHasManyData (MainModel $model1,Relation $relation): void 
    {
        $model2 = $relation->model;
        $FK = $model2->foreignKeys[$model1->table];
        $PK = $model1->primaryKey;
        $relationName = $relation->name;

        $helper = new _Array;
        foreach ($model2->data as  $value) {
            if($helper[$value[$FK]]) $helper[$value[$FK]][] = $value;
            else $helper[$value[$FK]] = [$value];
        }

        $model2->data->reset();

        foreach ($model1->data as &$value) {
            if(!$helper[$value[$PK]]) {
                $value[$relationName] = [];
                continue;
            }
            $value[$relationName] = $helper[$value[$PK]];
        }
    }

    private function injectHasManyCountData (MainModel $model1, Relation $relation): void 
    {
        $relationName = $relation->name;
        $model2 = $relation->model;

        $FK = $model2->foreignKeys[$model1->table];
        $PK = $model1->primaryKey;

        $helper = new _Array;
        foreach ($model2->data as  $value) {
            $helper[$value[$FK]] = $value;
        }

        $model2->data->reset();

        foreach ($model1->data as &$value) {
            if(!$helper[$value[$PK]]) {
                $value[$relationName] = 0;
                continue;
            }
            $value[$relationName] = $helper[$value[$PK]]['count'];
        }
    }
}