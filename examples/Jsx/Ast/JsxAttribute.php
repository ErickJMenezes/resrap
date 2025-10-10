<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class JsxAttribute
{
    public function __construct(
        public string $name,
        public string|Node $value
    ) {}
    
    public function toString(): string
    {
        if ($this->value instanceof Node) {
            return "{$this->name}={" . $this->value->toString() . "}";
        }
        
        return "{$this->name}=\"{$this->value}\"";
    }
}