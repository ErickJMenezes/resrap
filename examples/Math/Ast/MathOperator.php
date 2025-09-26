<?php

declare(strict_types=1);

namespace Resrap\Examples\Math\Ast;

final readonly class MathOperator implements Node
{
    public function __construct(public string $operator) {}
}
