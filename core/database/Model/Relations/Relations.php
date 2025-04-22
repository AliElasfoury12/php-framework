<?php 

namespace core\database\Model\Relations;

use core\App;
use core\base\_Array;
use core\database\Model\EagerLoading\EagerLoading;

class Relations {

    public _Array $relations;
    public _Array $withCount_relations;
    public ?CurrentRelation $currentRelation = null;
    public RELATIONSTYPE $Types;
    public BelongsTo $BelongsTo;
    public ManyToMany $ManyToMany;
    public HasMany $HasMany;
    public EagerLoading $eagerLoading;

    public function __construct() 
    {
        $this->relations = new _Array;
        $this->withCount_relations = new _Array;
        $this->currentRelation = new CurrentRelation;
        $this->Types = new RELATIONSTYPE;
        $this->BelongsTo = new BelongsTo;
        $this->ManyToMany = new ManyToMany;
        $this->HasMany = new HasMany;
        $this->eagerLoading = new EagerLoading;
    }

    public function commonData (string $class1, string $class2)
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        
        $currentRelation->table1 = $model->getClassTable($class1);
        $currentRelation->table2 = $model->getClassTable($class2);
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;

    }
}