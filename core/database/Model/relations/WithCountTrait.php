<?php 

namespace core\database\Model\relations;

use core\App;

trait WithCountTrait
{
    private static array $relationNames; 
    public static function withCount ($relationNames)//(likes) 
    {
        // get count of likes on post
               
        self::$relationNames = explode(',', $relationNames);

        return  new static;
    }

    public static function handleWithCount () 
    {
        $class = new static();

        $model = App::$app->model;
        $table1 =  $class->getClassTable();//posts
        $primaryKey = $model->getPK($table1);

        foreach (self::$relationNames as $relationName) {
            $forigenKey = $model->getFK($relationName, $table1);

            foreach ($model->relationData as &$item) {
                $id = $item[$primaryKey];
                $sql = "SELECT COUNT(*) FROM $relationName WHERE $forigenKey = '$id'";
                $count =  $model->fetch($sql)[0]['COUNT(*)'];
                $item[$relationName.'Count'] = $count ?? 0;
            }
        }
    }
}
