<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class Program implements Node
{
    /**
     * @param Node[] $statements
     */
    public function __construct(
        public array $statements
    ) {}
    
    public function toString(): string
    {
        return implode("\n", array_map(
            fn(Node $stmt) => $stmt->toString(),
            $this->statements
        ));
    }
}