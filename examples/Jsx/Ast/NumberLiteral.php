<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class NumberLiteral implements Node
{
    public function __construct(
        public string $value
    ) {}
    
    public function toString(): string
    {
        return $this->value;
    }
}