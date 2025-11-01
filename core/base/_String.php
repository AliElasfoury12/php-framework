<?php 

namespace core\base;

class _String
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

    public function __clone ()  
    {
        $this->string = ''.$this->string;
    }

    public function build (string $string): void  
    {
        $this->string .= $string;
    }

    public function contains(string $needle): bool
    {
        return str_contains($this->string, $needle);
    }

    public function explode(string $sepretor): _Array
    {
        return new _Array(explode($sepretor, $this->string));
    }

    public function length (): int  
    {
        return strlen($this->string);
    }

    public function position (string $string): bool|int
    {
        return strpos($this->string, $string);
    }

    public function preg_replace (string $pattern, string $replace): _String
    {
        $result = preg_replace($pattern, $replace, $this->string);
        return new self($result);
    }

    public function replace (string $search, string $replace): _String
    {
        $result = str_replace($search, $replace, $this->string);
        return new self($result);
    }

    public function replaceAll (array $replace): _String
    {
        $result = strtr($this->string, $replace);
        return new self($result);
    }

    public function reset (): void  
    {
        $this->string = '';
    }

    public function set(string $string): void
    {
        $this->string = $string;
    }

    public function subString (string $offset, string $length = null): _String
    {
        $result = substr($this->string, $offset, $length);
        return new self($result);
    }

    public function toLowerCase (): _String  
    {
        return new _String(strtolower($this->string));
    }

    public function value (): string  
    {
        return $this->string;
    }
}