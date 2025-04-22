<?php 

namespace core\Database\Model\EagerLoading;

use core\base\_Array;
use core\Database\Model\MainModel;
use core\Database\Model\Relations\CurrentRelation;
use core\Database\Model\Relations\RELATIONSTYPE;

class BuildEagerLoadingSQL {
    public function buildSQL (MainModel $model,_Array $relations, string $class, RELATIONSTYPE $relationsTypes): void
    {
        $PK = $model->PrimaryKey;
        $ids = $model->ids;
        $orderBy = $model->orderBy;
        $currentRelation = $model->relations->currentRelation;
        $table = $model->table;
        $class2 = $class;
        $sql = '';
        $select = '';
        $extraQuery = '';

        for ($i=0; $i < $relations->size; $i++) { 
            call_user_func([new $class2, $relations[$i]]);
            $table1 = $currentRelation->table1;
            $class2 = $currentRelation->model2;
            $currentRelation->name = $relations[$i];
            $type = $currentRelation->type;

            if($i > 0 && ($table1 === $table || $relations[$i - 1]->type == $relationsTypes::MANYTOMANY)) {
                $j = $i - 1;
                $table1 = "alias$j";
            }

            switch ($type) {
                case $relationsTypes::BELONGSTO:
                    if($i == $relations->size - 1) $model->relations->BelongsTo->select($currentRelation->columns);
                    $sql .= $this->buildBelongsToSQL($model, $currentRelation, $table1, $select, $extraQuery);
                break;

                case  $relationsTypes::HASMANY:
                    if($i == $relations->size - 1) $model->relations->HasMany->select($currentRelation->columns);
                    $sql .= $this->buildHasManySQL($model, $currentRelation, $table1, $select, $extraQuery);
                break;

                case $relationsTypes::MANYTOMANY:
                    if($i == $relations->size - 1) $model->relations->ManyToMany->select($currentRelation->columns);
                    $sql .= $this->buildManyToManySQL($model, $currentRelation, $table1, $i, $select, $extraQuery);
                break;
                
            }

            $currentRelation->sql = "SELECT $select,$table.$PK AS mainKey FROM $table $sql WHERE $table.$PK IN ($ids) $extraQuery $orderBy";
            $relations[$i] = clone $currentRelation;
        }
    }

    private function buildBelongsToSQL (MainModel $model, CurrentRelation $currentRelation, string $table1, string &$select, &$extraQuery): string
    {
        $PK2 = $currentRelation->PK2;
        $FK1 = $currentRelation->FK1;
        $table2 = $currentRelation->table2;

        $select = $model->relations->BelongsTo->query->getSelect($table2);
        $extraQuery = $model->relations->BelongsTo->query->getQuery($table2);
        $model->relations->BelongsTo->query->reset();

        return "INNER JOIN $table2 ON $table1.$FK1 = $table2.$PK2 ";
    }

    private function buildHasManySQL (MainModel $model, CurrentRelation $currentRelation, string $table1, string &$select, &$extraQuery): string
    {
        $PK1 = $currentRelation->PK1;
        $FK2 = $currentRelation->FK2;
        $table2 = $currentRelation->table2;

        $select = $model->relations->HasMany->query->getSelect($table2);
        $extraQuery = $model->relations->HasMany->query->getQuery($table2);
        $model->relations->HasMany->query->reset();

        return "INNER JOIN $table2 ON $table1.$PK1 = $table2.$FK2 ";
    }

    private function buildManyToManySQL (MainModel $model, CurrentRelation $currentRelation, string $table1, int $i, string &$select, &$extraQuery): string
    {
        $PK1 = $currentRelation->PK1;
        $pivotTable = $currentRelation->pivotTable;
        $pivotKey = $currentRelation->pivotKey;
        $relatedKey = $currentRelation->relatedKey;
        $table2 = $currentRelation->table2;
        $PK2 = $currentRelation->PK2;

        $select = $model->relations->ManyToMany->query->getSelect("alias$i");
        $extraQuery = $model->relations->ManyToMany->query->getQuery();
        $model->relations->ManyToMany->query->reset();

        return "INNER JOIN $pivotTable ON $table1.$PK1 = $pivotTable.$pivotKey 
        INNER JOIN $table2 AS alias$i ON $pivotTable.$relatedKey = alias$i.$PK2 ";
    }

}