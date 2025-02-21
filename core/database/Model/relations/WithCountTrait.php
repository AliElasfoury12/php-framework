<?php 

namespace core\database\Model\relations;

use core\App;

trait WithCountTrait
{
    private static array $relationNames; 
    public static function withCount (string $relationNames): static//(likes) 
    {
        // get count of likes on post
               
        self::$relationNames = explode(',', $relationNames);

        return  new static;
    }

    public static function handleWithCount (): void 
    {
        $model = App::$app->model;
        $primaryKey = $model->primaryKey;

        foreach (self::$relationNames as $relationName) {
            $forigenKey = $model->getFK($relationName, $model->table);

            foreach ($model->relationData as &$item) {
                $id = $item[$primaryKey];
                $sql = "SELECT COUNT(*) FROM $relationName WHERE $forigenKey = '$id'";
                $count =  $model->fetch($sql)[0]['COUNT(*)'];
                $item[$relationName.'Count'] = $count ?? 0;
            }
        }
    }
}
