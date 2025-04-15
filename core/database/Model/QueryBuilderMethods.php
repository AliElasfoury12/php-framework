<?php

namespace core\Database\Model;

interface QueryBuilderMethods 
{
    public function latest();
    public function paginate (int $perPage);
    public function select(string $columns);
    public function with(array $relations);
    public function withCount(string $columns);
}