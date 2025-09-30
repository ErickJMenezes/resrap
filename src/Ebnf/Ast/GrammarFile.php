<?php

declare(strict_types=1);

namespace Resrap\Component\Ebnf\Ast;

final readonly class GrammarFile implements Node
{
    /**
     * @param array<UseStatement>      $uses
     * @param array<GrammarDefinition> $grammarDefinitions
     */
    public function __construct(
        public string $classname,
        public array $uses,
        public array $grammarDefinitions,
    ) {}
}
