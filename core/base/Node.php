<?php 

namespace core\base;

class Node {
    public int $data;
    public ?Node $left = null;
    public ?Node $right = null;

    public function __construct(int $data) {
        $this->data = $data;
    }
}