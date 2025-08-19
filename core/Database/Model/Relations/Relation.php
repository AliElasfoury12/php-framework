<?php 

namespace core\Database\Model\Relations;

use core\Database\Model\MainModel;

class Relation {
    public string $name;
    public string $type;
    public string $pivotTable;
    public string $pivotKey;
    public string $relatedKey;
    public MainModel $model;
}