<?php

declare(strict_types=1);

namespace Resrap\Component\Grammar\Ast;

final readonly class RuleToken implements Node
{
    public const int EXPR = 1;
    public const int TOK = 2;
    public const int LITERAL = 3;

    public function __construct(
        public string $value,
        public int $kind,
    ) {}
}
