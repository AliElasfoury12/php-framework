<?php 

namespace core\database\Model\relations;

use core\App;

trait WithTrait
{
    public static function with (array $relations): static
    {
        App::$app->model->relations = $relations;

        return new static;
    }

    public static function handleWith(array $relations):array
    {
        $class = new static;
        $model = App::$app->model;
        
        foreach ($relations as $relation) { 

            if(str_contains($relation, ':')) //posts:id,post
            {
                $model->getRequestedColumns($relation);
                $relation = $model->relationName;
            }

            if(str_contains($relation, '.')) //posts.comments
            {
                Nested::run($class::class, $relation);
                $model->requestedCoulmns = '';
                continue;
            }

            $model->select($model->requestedCoulmns);
            call_user_func([$class, $model->relationName]);
            $model->handleRelation();
        }

        return $model->relationData;
    }
    /*
    $relations = [
        'belongsto' => [],
        'manyTomany' => [],
        'hasmany' => [],
        'nested' => [
            'belongsto' => [],
            'manyTomany' => [],
            'hasmany' => [],
        ]
    ]
    */
    private function getRequestedColumns (string $relation): void 
    {
        $model = App::$app->model;
        $colonPostion = strpos($relation,':');
        $model->relationName = substr($relation, 0, $colonPostion);
        $model->requestedCoulmns = substr($relation, $colonPostion + 1);
    }

    /*
    posts
    X posts:id,name
    posts.comments
    X posts.comments:id
     */

    protected function handleRelation (): void
    {
        $model = App::$app->model;
        $type = $model->currentRelation->type;
        $RelationsTypes = $model->relationTypes;

        switch ($type) {
            case $RelationsTypes::HASMANY:
                HasMany::run();
            break;

            case $RelationsTypes::BELONGSTO:
                BelongsTo::run();
            break;

            case $RelationsTypes::HASONE:
                BelongsTo::run();
            break;

            case $RelationsTypes::MANYTOMANY:
                ManyToMany::run();
            break;
        }
    }    
}


