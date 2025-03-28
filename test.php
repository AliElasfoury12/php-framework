<?php

declare(strict_types=1);

class Relations {
    public function __construct() {
        echo "relations start\n";
    }
}
class BelongsTo extends Relations {

    public function fun1 ()
    {
        echo 'a'."\n";
        return $this;
    }

    public function fun2 ()
    {
        return $this;
    }
    
}

$BelongsTo = new BelongsTo;

