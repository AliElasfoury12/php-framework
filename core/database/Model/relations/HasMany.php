<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;
class HasMany extends RelationQueryBuilder {
    public function run (): void 
    {
        $model = App::$app->model;
        $sql = $this->prpareSQL();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);
        $this->inject_data($data);
        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }
    private function prpareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $foreignKey = $currentRelation->FK2;
        $primaryKey = $currentRelation->PK1;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect($table2);
        $query = $this->query->getQuery($table2);
       
        return "SELECT $select FROM $table1
        INNER JOIN $table2 ON $table1.$primaryKey = $table2.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (_Array $data): void 
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;

        $foreignKey = $currentRelation->FK2;
        $primaryKey = $currentRelation->PK1;

        $i = 0;
        foreach ($model->data as &$item) {
            $item[$currentRelation->name] = [];

            while($i < $data->size && $item[$primaryKey] == $data[$i][$foreignKey]){
                $item[$currentRelation->name][] = $data[$i];
                $i++;
            }
        }
    }
}