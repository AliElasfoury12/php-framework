<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\base\_Array;
use core\Database\Model\MainModel;

class ManyToMany 
{
    public function createRelation (MainModel $model1, string $class2, string $pivotTable, string $pivotKey, string $relatedKey): MainModel  
    {
        $relation = $model1->RelationsClass->sigenCommonRelationData($model1,$class2);
        $model2 = $relation->model;
        $relation->type = RELATIONSTYPE::MANYTOMANY;

        $relation->pivotTable = $this->getPivotTable($pivotTable,$model1,$model2);
        $relation->pivotKey = $this->getPivotKey($pivotKey, $relation, $model1);
        $relation->relatedKey = $this->getRelatedKey($relatedKey,$relation);
        $relation->model->primaryKey = App::$app->db->getPK($relation->model->table);

        return $model2;
    }

    private function getPivotTable (string $pivotTable, MainModel $model1, MainModel $model2): string 
    {
        if($pivotTable) return $pivotTable;

        $class1 = rtrim($model1->table,'s');
        $class2 = rtrim($model2->table,'s');
        if($class1[0] < $class2[0]) $pivotTable = $class1."_".$class2;
        else $pivotTable = $class2."_".$class1;
        
        return $pivotTable;
    }

    private function getPivotKey (string $pivotKey, Relation $relation, MainModel $model1):string 
    {
        if($pivotKey) return $pivotKey;
        return App::$app->db->getFK($relation->pivotTable, $model1->table);
    }

    private function getRelatedKey (string $relatedKey, Relation $relation):string 
    {
        $model2 = $relation->model;
        if($relatedKey) return $relatedKey;
        return App::$app->db->getFK($relation->pivotTable, $model2->table);
        
    }

    public function handleRelation (MainModel $model1, Relation $relation): void 
    {
        $model2 = $relation->model;
        $this->getManyToManyData($model1, $model2, $relation);  
        if(!$model2->relations->empty()) $model2->handleRelations(false);
        $this->injectManyToManyData($model1,$model2,$relation);
    }

    private function getManyToManyData (MainModel $model1, MainModel $model2, Relation $relation): void
    {
        if($model1->table == $model2->table) $model2->alias = 'alias0';
        if(!$model1->ids) $model1->getActiveIds();

        $sql = $this->createSql($model1, $relation);
        $model2->data = App::$app->db->fetch($sql);
    }

    private function injectManyToManyData  (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $PK = $model1->primaryKey;
        $relationName = $relation->name;
 
        $helper = new _Array;
        foreach ($model2->data as  $value) {
            if($helper[$value['pivotKey']]) $helper[$value['pivotKey']][] = $value;
            else $helper[$value['pivotKey']] = [$value];
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

    private function createTableJoin (string $pivTable,MainModel $model1,MainModel $model2,Relation $relation):string
    {
        $pivotSql = "INNER JOIN $pivTable ON {$model1->table}.{$model1->primaryKey} = $pivTable.{$relation->pivotKey}";

        if($model2->alias){
            $tableJoin = "$pivotSql INNER JOIN {$model2->table} AS {$model2->alias} ON $pivTable.{$relation->relatedKey} = {$model2->alias}.{$model2->primaryKey}";
        }else{
            $tableJoin = "$pivotSql INNER JOIN {$model2->table} ON $pivTable.{$relation->relatedKey} = {$model2->table}.{$model2->primaryKey}";
        }

        return $tableJoin;
    }

    private function createSql (MainModel $model1, Relation $relation): string
    {
        $model2 = $relation->model;
        $pivTable = $relation->pivotTable;

        $tableJoin = $this->createTableJoin($pivTable,$model1,$model2,$relation);
        $select = $model2->query->getSelect($model2->alias?:$model2->table).", $pivTable.{$relation->pivotKey} AS pivotKey";
        $model2->where("{$model1->table}.{$model1->primaryKey}",'IN', $model1->ids);
        $query = $model2->query->getQuery();
        
        return "SELECT $select FROM {$model1->table} $tableJoin $query";
    }
}