<?php 

declare(strict_types=1);

namespace core\base;

use ArrayAccess;
use Closure;
use core\App;
use IteratorAggregate;
use Traversable;

class _Array implements ArrayAccess, IteratorAggregate
{
    private array $array = [] ;
    public int $size = 0;

    public function __construct(array $array = [])
    {
        foreach ($array as $key => $value) {
            $this[$key] = $value;
        }
        if($array) $this->size = count($array);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->array[$offset]);
    }

    public function &offsetGet(mixed $offset): mixed 
    {
        $a = null;
        if($offset instanceof _String) $offset = $offset->value();
        if(!isset($this->array[$offset])) return $a;
        $this->array[$offset] = is_callable($this->array[$offset]) ? $this->array[$offset]($this) : $this->array[$offset];
        return $this->array[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(is_array($value)) $value = new self($value);

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

    public function getIterator():Traversable
    {
        return $this->iteratorByRefernce();
    }

    public function &iteratorByRefernce()
    {
        foreach ($this->array as $key => &$value) {
            yield $key => $value;
        }
    }

    public function __get(mixed $name): mixed 
    {
        if(!isset($this->array[$name])) return null;
        return $this->array[$name];
    }

    public function __set($name, $value)   
    {
        $this->array[$name] = $value;
    }

    public function __clone ()  
    {
        foreach ($this->array as $key => $value) {
            if(is_object($value)){
                $this->array[$key] = clone $value;
            }
        }
    }

    public function diff (array $array): array
    {
        return array_diff($array, $this->array);
    }

    public function empty (): bool 
    {
        return $this->array === [];
    }

    public function hasKey (string $key): bool 
    {
        return array_key_exists($key, $this->array);
    }

    public function implode (string $seprator): string 
    {
        return implode($seprator,$this->array);
    }

    public function map (Closure $callback): _Array
    {
        $newArray = array_map($callback, $this->array);
        return new _Array($newArray);
    }

    public function merge (array $array): _Array
    {
       $result = array_merge($this->array, $array);
       return new self($result);
    }

    public function pop (): void 
    {
        array_pop($this->array);
        $this->size--;
    }

    public function print (): void 
    {
        $array = clone $this;
        App::dump( $array->toArray());
    }

    public function reset (): void 
    {
        $this->array = [];
        $this->size = 0;
    }

    public function set (array $array): void 
    {
        $this->array = $array;
        $this->size = count($array);
    }

    public function toArray (): array 
    {
        foreach ($this->array as &$value) {
            if($value instanceof _Array){
                $value = $value->toArray();
            }
        }
        return $this->array;
    }
}

/*
$array = new _Array();
var_dump($array->empty());
$array[] = 1;
$array[] = 2;
var_dump($array->empty());

 //$array->map(fn($num) => $num * 2);
 foreach ($array as $key => &$value) {
   $value *=2 ;
 }
print_r($array);
*/