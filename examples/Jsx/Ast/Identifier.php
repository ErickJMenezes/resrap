<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class Identifier implements Node
{
    public function __construct(
        public string $name
    ) {}
    
    public function toString(): string
    {
        return $this->name;
    }
}