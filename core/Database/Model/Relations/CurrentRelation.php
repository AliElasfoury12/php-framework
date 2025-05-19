<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\base\_Array;

class CurrentRelation 
{
    public string $type;
    public string $table1;
    public string $alias = '';
    public string $table2;
    public string $PK1;
    public string $PK2;
    public string $FK1;
    public string $FK2;
    public string $pivotTable;
    public string $pivotKey;
    public string $relatedKey;
    public string $name;
    public string $model1;
    public string $model2 ;
    public string $sql;
    public _Array $with;
    public _Array $withCount;


    public function __construct() {
        $this->withCount = new _Array;
        $this->with = new _Array;
    }

    public function __clone ()  
    {
        foreach ((array) $this as $key => $value) {
           if(is_object($value)){
            $this->$key = clone $value;
           }
        }
    }

    public function print ()  
    {
        App::dump([$this]);
    }

    public function reset (): void
    {
        foreach ((array) $this as $key => $value) {
            if(is_object($value)){
                $value->reset();
            }else if(is_string($value)){
                $this->$key = '';
            }
        }
    }
}