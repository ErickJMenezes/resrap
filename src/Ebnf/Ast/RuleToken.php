<?php

declare(strict_types=1);

namespace Resrap\Component\Ebnf\Ast;

final readonly class RuleToken implements Node
{
    public function __construct(
        public string $value,
        public bool $literal,
    ) {}
}
