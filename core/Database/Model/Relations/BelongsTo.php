<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\Database\Model\MainModel;

class BelongsTo 
{
    public function createRelation (MainModel $model1, string $class2, string $foreignKey, string $primaryKey): MainModel  
    {
        $relation = $model1->RelationsClass->sigenCommonRelationData($model1,$class2);
        $model2 = $relation->model;
        $relation->type = RELATIONSTYPE::BELONGSTO;
        if($foreignKey) $model1->foreignKeys[$model2->table] = $foreignKey;
        if($primaryKey) $model2->primaryKey = $primaryKey;
        else  $model2->getPrimaryKey();
        return $model2;
    }
    public function handleRelation (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $this->getBelongsToData($model1,$model2);
        if(!$model2->relations->empty())  $model2->handleRelations(false);
        $this->injectBelongsToData($model1,$model2,$relation);
    }

    private function getBelongsToData (MainModel $model1, MainModel $model2): void 
    {
        $table1 = '';
        $table2 = '';
        $model1->RelationsClass->handleAliases($table1, $table2,$model1,$model2);

        $tableJoin = $this->createTableJoin($table1, $table2,$model1,$model2);

        $select = $model2->query->getSelect($table2);
        $sql = $model2->prepareSQl($model1, $tableJoin,$select);

        $model2->data = App::$app->db->fetch($sql);
    }

    private function injectBelongsToData (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $foreigenKey = $model1->foreignKeys[$model2->table];
        $primaryKey = $model2->primaryKey;
        $relationName = $relation->name;

        $i = 0;
        foreach ($model1->data as  &$value) {
            if($value[$foreigenKey] == $model2->data[$i][$primaryKey]){ 
                $value[$relationName] = $model2->data[$i];
                $i++;
            }else  
                $value[$relation->name] = [];
        }

        //$model2->data->reset();
    }

    private function createTableJoin (string $table1, string $table2,MainModel $model1, MainModel $model2):string
    {
        $foreigenKey = $model1->getRelatedForigenKey($model2);

        $joinCondition = "{$table2}.{$model2->primaryKey} = {$table1}.{$foreigenKey}";

        if($model2->alias){
            $tableJoin = "INNER JOIN {$model2->alias} AS {$table2} ON $joinCondition";
        }else 
            $tableJoin = "INNER JOIN {$table2} ON $joinCondition";

        return $tableJoin;
    }
}