<?php

declare(strict_types=1);

namespace Resrap\Examples\Math\Ast;

final readonly class MathExpression implements Node
{
    /**
     * @param array<Node> $nodes
     */
    public function __construct(
        public array $nodes,
    ) {}
}
