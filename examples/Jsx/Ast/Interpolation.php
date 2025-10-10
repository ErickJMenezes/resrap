<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class Interpolation implements Node
{
    public function __construct(
        public Node $expression
    ) {}
    
    public function toString(): string
    {
        return "{" . $this->expression->toString() . "}";
    }
}