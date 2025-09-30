<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf\Ast;

final readonly class GrammarDefinition implements Node
{
    /**
     * @param string                       $name
     * @param array<GrammarRuleDefinition> $rules
     */
    public function __construct(
        public string $name,
        public array $rules,
    ) {}
}
