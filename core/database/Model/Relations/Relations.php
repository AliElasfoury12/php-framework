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
}