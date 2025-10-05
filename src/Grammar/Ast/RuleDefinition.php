<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Ast;

final readonly class RuleDefinition implements Node
{
    /**
     * @param array<RuleToken> $tokens
     */
    public function __construct(
        public array $tokens,
        public string $codeBlock
    ) {}
}
