<?php 

namespace core\database\Model;

use core\App;
use core\database\Model\relations\Relations;

class MainModel extends Relations {
    use ModelMethodsTrait, SQLTrait, Create, InsertArr;

    public function fetch(string $sql, string $type = ''): array 
    {
        return App::$app->db->fetch($sql, $type);
    }
   
}