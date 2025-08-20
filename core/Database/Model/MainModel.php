<?php 

namespace core\Database\Model;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\Query\QueryBuilder;
use core\Database\Model\Relations\Relations;
use core\Database\Model\Relations\RELATIONSTYPE;

class MainModel extends QueryBuilder 
{
    public string $table;
    public string $primaryKey;
    public _Array $foreignKeys;
    public _Array $relations;
    public _Array $data;
    public string $ids = '';
    public string $alias = '';
    public Relations $RelationsClass;

    public function __construct() {
        parent::__construct();
        $this->relations = new _Array;
        $this->table = $this->getTable($this::class);
        $this->foreignKeys = new _Array;
        $this->RelationsClass = new Relations;
    }

    public function getTable(string $class): string 
    {
        $class = new _String($class);
        $class = $class->replace('app\\models\\', '');
        return $this->ToCamelCase($class).'s';
    }

    private function ToCamelCase (_String $string): string 
    {
        return $string->preg_replace('/(?<!^)[A-Z]/', '_$0')->toLowerCase();
    }

    public function getPrimaryKey (): void  
    {
        $this->primaryKey = App::$app->db->getPK($this->table);
    }

    public function handleRelations (bool $isMainModel = true): void  
    {
        $this->getPrimaryKey();

        if($isMainModel){
            $this->RelationsClass->joiningKey = "{$this->table}.{$this->primaryKey}";
            $this->getActiveIds();
            $this->query->sql = $this->query->sql
            ->replace('{query}', "WHERE {$this->table}.{$this->primaryKey} IN ({$this->ids}) {query}");
        }

        foreach ($this->relations as $relation) {
            $model2 = $relation->model;
            $model2->RelationsClass->joiningKey = $this->RelationsClass->joiningKey;
            
            switch ($relation->type) {
                case RELATIONSTYPE::BELONGSTO:
                    $this->RelationsClass->BelongsTo->handleRelation($this,$model2, $relation);
                break;
                case RELATIONSTYPE::HASMANY:
                    $this->RelationsClass->HasMany->handleRelation($this,$model2, $relation);
                break;
                default:
                    $this->RelationsClass->ManyToMany->handleRelation($this,$model2, $relation);
                break;
            }
        }

        //if($isMainModel) App::dump([$this]);
    }

    public function getActiveIds (): void  
    {
        foreach ($this->data as  $value) {
            $this->ids .= $value['id'].',';
        }
        $this->ids = substr($this->ids,0,-1);
    }

    public function getRelatedForigenKey (MainModel $model2):string
    {
        if($this->foreignKeys[$model2->table])
            $foreigenKey = $this->foreignKeys[$model2->table];
        else{
            $foreigenKey = App::$app->db->getFK($this->table, $model2->table);
            $this->foreignKeys[$model2->table] = $foreigenKey;
        }
        return $foreigenKey;
    }
    
    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): MainModel
    {
        return $this->RelationsClass->BelongsTo
        ->createRelation($this,$class2,$foreignKey,$primaryKey);
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): MainModel
    {
        return $this->RelationsClass->HasMany
        ->createRelation($this,$class2,$foreignKey,$primaryKey);
    }
    
    public function manyToMany (string $class2, string $pivotTable = '', string $pivotKey = '', string $relatedKey = ''): MainModel
    {
        return $this->RelationsClass->ManyToMany
        ->createRelation($this,$class2,$pivotTable,$pivotKey,$relatedKey);
    }

    public function createAlias (_String $sql, string $table): string 
    {
        if($sql->contains("FROM $table") || $sql->contains("INNER JOIN $table")){
            for ($j=0; $j >=0 ; $j++) { 
                if(!$sql->contains("alias$j")) {
                    $this->alias = "alias$j";
                    return "alias$j";
                }
            }

        }
        return $table;
    }

    public function prepareSQl (MainModel $model, string $tableJoin, string $select): _String 
    {
        $this->query->sql = $model->query->sql->replace('{tableJoin}' , "$tableJoin {tableJoin}");
        
        return $this->query->sql->replaceAll( [
            "{select}" => "{$model->RelationsClass->joiningKey} AS joiningKey, $select",
            '{tableJoin}' => '',
            '{query}' => $this->query->getQuery()
        ]);
    }

}