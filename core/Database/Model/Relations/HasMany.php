<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\Database\Model\MainModel;

class HasMany  {

    public function createRelation (MainModel $model1, string $class2, string $foreignKey, string $primaryKey): MainModel  
    {
        $relation = $model1->RelationsClass->sigenCommonRelationData($model1,$class2);
        $model2 = $relation->model;
        $relation->type = RELATIONSTYPE::HASMANY;

        if($foreignKey) $model2->foreignKeys[$model1->table] = $foreignKey;
        if($primaryKey) $model1->primaryKey = $primaryKey;
        return $model2;
    }
    public function handleRelation (MainModel $model1, MainModel $model2, Relation $relation): void 
    {
        $model2 = $relation->model;
        $this->getHasManyData($model1,$model2);
        if(!$model2->relations->empty()) $model2->handleRelations(false);
        $this->injectHasManyData($relation,$model1,$model2);
    }

    private function getHasManyData (MainModel $model1, MainModel $model2): void 
    {
        $table1 = ''; $table2 = '';
        $model1->RelationsClass->handleAliases($table1, $table2,$model1,$model2);

        $tableJoin = $this->createTableJoin($table1,$table2,$model1,$model2);

        $select = $model2->query->getSelect($model2->table);
        $sql = $model2->prepareSQl($model1, $tableJoin,$select);

        $model2->data = App::$app->db->fetch($sql);
    }

    private function injectHasManyData (Relation $relation, MainModel $model1, MainModel $model2): void 
    {
        $FK = $model2->foreignKeys[$model1->table];
        $PK = $model1->primaryKey;

        $condition = fn($i, $value): bool => 
            $i < $model2->data->size && 
            $value[$PK] == $model2->data[$i][$FK] &&
            ($value[0] == null || $value[$PK] != $value[0][$PK]);

        $i = 0;
        foreach ($model1->data as &$value) {
            $value[$relation->name] = [];
            while ($condition($i, $value)) {
                $value[$relation->name][] = $model2->data[$i];
                $i++;
            }
        }

      //  $model2->data->reset();
    }

    private function createTableJoin (string $table1, string $table2,MainModel $model1, MainModel $model2):string
    {
        $forigenKey = $model2->getRelatedForigenKey($model1);
        $joinCondition = "$table2.{$forigenKey} =$table1.{$model1->primaryKey}";

        $tableJoin = "INNER JOIN {$model2->table} ON $joinCondition";

        if($model2->alias){
            $tableJoin = "INNER JOIN {$model2->alias} AS {$table2} ON $joinCondition";
        }else 
            $tableJoin = "INNER JOIN {$table2} ON $joinCondition";

        return $tableJoin;
    }

}