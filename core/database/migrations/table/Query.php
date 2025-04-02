<?php 

namespace core\database\migrations\table;

class Query {
    public array $create = [];
    public array $drop = [];
    private int $size = 0;

    public function add (string $field): void 
    {
        $this->create[] = $field;
        $this->size++;
    }

    public function drop (string $field): void 
    {
        $this->drop[] = $field;
        $this->size++;
    }

    public function pop (): void
    {
        array_pop($this->create);
        $this->size--;
    }

    public function last ():string 
    {
        return $this->create[$this->size - 1];
    }

    public function setLast (string $value): void 
    {
        $this->create[$this->size - 1] = $value;
    }
}