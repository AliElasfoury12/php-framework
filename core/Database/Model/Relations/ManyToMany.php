<?php 

namespace core\Database\Model\Relations;

use core\App;
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
        $relation->relatedKey = $this->getRelatedKey($relatedKey,$relation,$model2);
        $relation->model->primaryKey = App::$app->db->getPK($relation->model->table);

        return $model2;
    }
    public function handleRelation (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $this->getManyToManyData($model1, $model2, $relation);  
        if(!$model2->relations->empty()) $model2->handleRelations(false);
        $this->injectManyToManyData($model1,$model2,$relation);
    }

    private function getManyToManyData (MainModel $model1, MainModel $model2, Relation $relation): void
    {
        $table1 = ''; $table2 = '';
        $model1->RelationsClass->handleAliases($table1, $table2,$model1,$model2);
        
        $pivTable = $relation->pivotTable;

        $tableJoin = $this->createTableJoin(
            $table1,
            $table2, 
            $pivTable, 
            $model1,
            $model2,
            $relation
        );

        $select = $model2->query->getSelect($table2).", $pivTable.{$relation->pivotKey} AS pivotKey";
        $sql = $model2->prepareSQl($model1,$tableJoin, $select);
        $model2->data = App::$app->db->fetch($sql);
    }

    private function injectManyToManyData  (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $PK = $model1->primaryKey;
        $relationName = $relation->name;

        $condition = fn (int $i, $value): bool =>
            $i < $model2->data->size &&
            $value[$PK] == $model2->data[$i]['pivotKey'] &&
            ($value[$relationName][0] == null || $value[$relationName][0]['pivotKey'] != $model2->data[$i]['pivotKey']);
        
        $i = 0;
        foreach ($model1->data as &$value) {
            $value[$relation->name] = [];
            while ($condition($i, $value)) {
                $value[$relationName][] = $model2->data[$i];
                $i++;
            }
        }

        $model2->data->reset();
    }

    private function createTableJoin (
        string $table1,
        string $table2, 
        string $pivTable, 
        MainModel $model1, 
        MainModel $model2, 
        Relation $relation):string
    {
        $pivotSql = "INNER JOIN $pivTable ON $table1.{$model1->primaryKey} = $pivTable.{$relation->pivotKey}";
        $tableJoin = "$pivotSql INNER JOIN {table2} ON $pivTable.{$relation->relatedKey} = $table2.{$model2->primaryKey}";

        if($model2->alias){
            $tableJoin = str_replace("{table2}","{$model2->table} AS $table2",$tableJoin);
        }else{
            $tableJoin = str_replace("{table2}",$table2,$tableJoin);
        }

        return $tableJoin;
    }

    private function getPivotTable  (string $pivotTable, MainModel $model1, MainModel $model2): string 
    {
        if(!$pivotTable) {
            $class1 = rtrim($model1->table,'s');
            $class2 = rtrim($model2->table,'s');
            if($class1[0] < $class2[0]) $pivotTable = $class1."_".$class2;
            else $pivotTable = $class2."_".$class1;
        }
        return $pivotTable;
    }

    private function getPivotKey (string $pivotKey, Relation $relation, MainModel $model1):string 
    {
        if(!$pivotKey)$pivotKey = App::$app->db->getFK($relation->pivotTable, $model1->table);
        return $pivotKey;
    }

    private function getRelatedKey (string $relatedKey, Relation $relation, MainModel $model2):string 
    {
        if(!$relatedKey) $relatedKey = App::$app->db->getFK($relation->pivotTable, $model2->table);
        return $relatedKey;
    }
}