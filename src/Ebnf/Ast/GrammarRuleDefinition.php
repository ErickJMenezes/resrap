<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf\Ast;

final readonly class GrammarRuleDefinition implements Node
{
    /**
     * @param array<RuleToken> $tokens
     */
    public function __construct(
        public array $tokens,
        public string $codeBlock
    ) {}
}
