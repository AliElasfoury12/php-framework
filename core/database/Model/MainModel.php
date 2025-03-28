<?php 

namespace core\database\Model;

use core\App;
use core\database\Model\relations\Relations;

class MainModel extends QueryBuilder {
    use SQLTrait, Create, InsertArr ;
    public Relations $relations;

    public function __construct() {
        $this->relations = new Relations;
    }

    public function fetch(string $sql, string $type = ''): array 
    {
        return App::$app->db->fetch($sql, $type);
    }
   
}