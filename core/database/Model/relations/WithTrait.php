<?php 

namespace core\database\Model\relations;

use core\App;

trait WithTrait
{
    public static function with (array $relations)
    {
        App::$app->model->relations = $relations;

        return new static;
    }

    public static function handleWith(array $relations):array
    {
        $class = new static();

        $model = App::$app->model;
        
        foreach ($relations as $relation) { 

            if(str_contains($relation, ':')) //posts:id,post
            {
                $model->handleRequestedColumns($relation);
                $relation = $model->relationName;
            }

            if(str_contains($relation, '.')) //posts.comments
            {
                Nested::run($class, $relation);
                continue;
            }

            $model->relationName = $relation;
            call_user_func([new static, $relation]);
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
    private function handleRequestedColumns ($relation) 
    {
        $model = App::$app->model;
        preg_match("/([\w\.]+):\s*([\w+,\s*]+)/",$relation, $match);
        $model->relationName = $match[1];//posts
        $model->requestedCoulmns[$model->relationName] = $match[2]; //reqcol[posts] id,post
    }

    /*
    posts
    X posts:id,name
    posts.comments
    X posts.comments:id
     */

    protected function handleRelation (): void 
    {
        $type = App::$app->model->currentRelation['type'];

        switch ($type) {
            case 'HASMANY':
                HasMany::run();
            break;

            case 'BELONGSTO' :
                BelongsTo::run();
            break;

            case 'HASONE':
                BelongsTo::run();
            break;

            case 'MANYTOMANY':
                ManyToMany::run();
            break;
        }
    }    
}
