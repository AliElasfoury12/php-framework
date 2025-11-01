<?php 

namespace core\Database\Model;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\base\OrderSet;
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
        if($isMainModel) $this->getActiveIds($isMainModel);

        foreach ($this->relations as $relation) {            
            switch ($relation->type) {
                case RELATIONSTYPE::BELONGSTO:
                    $this->RelationsClass->BelongsTo->handleRelation($this,$relation);
                break;
                case RELATIONSTYPE::HASMANY:
                    $this->RelationsClass->HasMany->handleRelation($this,$relation);
                break;
                default:
                    $this->RelationsClass->ManyToMany->handleRelation($this,$relation);
                break;
            }
        }

       // if($isMainModel) App::dump([$this]);
    }

    public function getActiveIds (bool $isMainModel = false): void  
    {
        if(!$isMainModel) {
            $orderSet = new OrderSet;
            foreach ($this->data as  $value) {
                $orderSet->add($value[$this->primaryKey]);
            }

            $this->ids = $orderSet->join();
            return;
        }

        foreach ($this->data as  $value) {
            $this->ids .= $value[$this->primaryKey].',';
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
}