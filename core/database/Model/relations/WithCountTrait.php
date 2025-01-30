<?php 

namespace core\database\Model\relations;

use core\App;

trait WithCountTrait
{
    public static function withCount ($relationNames)//(likes) 
    {
        // get count of likes on post
        $class = get_called_class();
        $class = new $class();

        $model = App::$app->model;
        $table1 =  $class->getTableName();//posts
        $primaryKey = $model->getPK($table1);

        if(!$model->relationData){
            $select = $model->handleSelect();
            $query = $model->getQuery();

            $sql = "SELECT $select FROM $table1 $query";
            //echo "$sql <br>";
            $model->relationData = $model->fetch($sql);
        }

        $relationNames = explode(',', $relationNames);

        foreach ($relationNames as $relationName) {
            $forigenKey = $model->getFK($relationName, $table1);

            foreach ($model->relationData as &$item) {
                $id = $item[$primaryKey];
                $sql = "SELECT COUNT(*) FROM $relationName WHERE $forigenKey = '$id'";
                $count =  $model->fetch($sql)[0]['COUNT(*)'];
                $item[$relationName.'Count'] = $count ?? 0;
            }
        }

        return  $class;
    }
}
