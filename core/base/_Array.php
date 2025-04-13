<?php 

declare(strict_types=1);

namespace core\base;

use ArrayAccess;
use ArrayObject;
use Closure;
class _Array implements ArrayAccess
{
    private array $array ;
    public int $size = 0;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->array[$offset]);
    }

    public function &offsetGet(mixed $offset): mixed 
    {
        if(!@$this->array[$offset]) return '';
        $this->array[$offset] = is_callable($this->array[$offset]) ? $this->array[$offset]($this) : $this->array[$offset];
        return $this->array[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if($offset === null) {
            $this->array[] = $value;
            $this->size++;
        }else {
            if(!$this[$offset]) $this->size++;
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void 
    {
        if($this[$offset]) $this->size--;
        unset($this->array[$offset]);
    }


    public function empty (): bool 
    {
        return $this->array === [];
    }

    public function implode (string $seprator): string 
    {
        return implode($seprator,$this->array);
    }

    public function map (Closure $callback): void 
    {
        foreach ($this->array as &$value) {
            $value = $callback($value);
        }
    }

    public function pop (): void 
    {
        array_pop($this->array);
        $this->size--;
    }

    public function reset (): void 
    {
        $this->array = [];
        $this->size = 0;
    }
}
/*
$array = new _Array();
var_dump($array->empty());
$array[] = 1;
$array[] = 2;
var_dump($array->empty());

 $array->map(fn($num) => $num * 2);
print_r($array);
*/