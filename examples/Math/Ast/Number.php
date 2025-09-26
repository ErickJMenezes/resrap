<?php

declare(strict_types=1);

namespace Resrap\Examples\Math\Ast;

final readonly class Number implements Node
{
    public function __construct(public string $number) {}
}
