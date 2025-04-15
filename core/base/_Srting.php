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

    public function replace (string $search, string $replace): _Srting
    {
        $result = str_replace($search, $replace, $this->string);
        return new self($result);
    }

    public function pregReplace (string $pattern, string $replace): _Srting
    {
        $result = preg_replace($pattern, $replace, $this->string);
        return new self($result);
    }
}