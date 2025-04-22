<?php 

namespace core\base;

class _Srting
{
    private string $string;

    public function __construct(string $string = '') 
    {
        $this->string = $string;
    }

    public function __tostring(): string
    {
        return $this->string;
    }

    public function contains(string $needle): bool
    {
        return str_contains($this->string, $needle);
    }

    public function explode(string $sepretor): _Array
    {
        return new _Array(explode($sepretor, $this->string));
    }

    public function position (string $string): bool|int
    {
        return strpos($this->string, $string);
    }

    public function pregReplace (string $pattern, string $replace): _Srting
    {
        $result = preg_replace($pattern, $replace, $this->string);
        return new self($result);
    }

    public function replace (string $search, string $replace): _Srting
    {
        $result = str_replace($search, $replace, $this->string);
        return new self($result);
    }

    public function set(string $string): void
    {
        $this->string = $string;
    }

    public function subString (string $offset, string $length = null): _Srting
    {
        $result = substr($this->string, $offset, $length);
        return new self($result);
    }
}