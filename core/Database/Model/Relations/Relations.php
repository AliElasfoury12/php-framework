<?php 

namespace core\Database\Model\Relations;

use core\Database\Model\MainModel;

class Relations {
    public BelongsTo $BelongsTo;
    public ManyToMany $ManyToMany;
    public HasMany $HasMany;

    public function __construct() 
    {
        $this->BelongsTo = new BelongsTo;
        $this->ManyToMany = new ManyToMany;
        $this->HasMany = new HasMany;
    }

    public function signCommonRelationData (MainModel $model, string $class2): Relation 
    {
        $relation = new Relation;
        $relation->model = new $class2;
        $callStack = debug_backtrace();
        $i = 3;
        while ($callStack[$i]['function'] != 'with' && $callStack[$i]['function'] != 'withCount') {
            $i++;
        }

        if($callStack[$i-1]['function'] == 'handleRelationWithColumns' || $callStack[$i-1]['function'] == 'handleNestedRelation') $i--;
        $relation->name = $callStack[$i-1]['function'];
        if($callStack[$i]['function'] == 'withCount') $relation->name .= '_count';
        $model->relations[$relation->name] = $relation;
        return $relation;
    }

}