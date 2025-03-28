<?php 

namespace core\database\Model;

class Query {
    public array $where = [];
    public array $select = [];
    public array $extraQuery = [];

    public function __construct() {
        $this->where = [];
        $this->select = [];
        $this->extraQuery = [];
    }

    public function reset (): void
    {
        $this->where = [];
        $this->select = [];
        $this->extraQuery = [];
    }
}