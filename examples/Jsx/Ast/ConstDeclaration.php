<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class ConstDeclaration implements Node
{
    public function __construct(
        public string $name,
        public Node $value
    ) {}
    
    public function toString(): string
    {
        return "const {$this->name} = {$this->value->toString()};";
    }
}