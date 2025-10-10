<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use Resrap\Component\Grammar\Ast\Directive;
use Resrap\Component\Grammar\Ast\GrammarDefinition;
use Resrap\Component\Grammar\Ast\GrammarFile;

final class GrammarFileBuilder
{
    /**
     * @param array<Directive>         $directives
     * @param array<GrammarDefinition> $definitions
     */
    public function build(array $directives, array $definitions): GrammarFile
    {
        $uses = [];
        $className = null;
        $start = null;
        $namespace = null;

        foreach ($directives as $directive) {
            switch ($directive->name) {
                case 'namespace':
                    $namespace = $directive->value;
                    break;
                case 'class':
                    $className = $directive->value;
                    break;
                case 'start':
                    $start = $directive->value;
                    break;
                case 'use':
                    $uses[] = $directive;
                    break;
            }
        }

        $missing = [];
        if (null === $className) {
            $missing[] = '%class';
        }
        if (null === $start) {
            $missing[] = '%start';
        }
        if (count($missing) > 0) {
            throw GrammarException::missingDirectives($missing);
        }

        return new GrammarFile(
            $namespace,
            $className,
            $uses,
            $start,
            $definitions,
        );
    }
}
