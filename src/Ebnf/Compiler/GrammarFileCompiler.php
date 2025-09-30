<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf\Compiler;

use Resrap\Component\Ebnf\Ast\GrammarDefinition;
use Resrap\Component\Ebnf\Ast\GrammarFile;
use Resrap\Component\Ebnf\Ast\GrammarRuleDefinition;
use Resrap\Component\Ebnf\Ast\UseStatement;
use Resrap\Component\Ebnf\EbnfParser;

final class GrammarFileCompiler
{

    public function __construct(private readonly EbnfParser $parser) {}

    public function compile(string $input): string
    {
        $grammarFile = $this->parser->parse($input);
        return $this->compileGrammarDefinitions($grammarFile);
    }

    private function compileGrammarDefinitions(GrammarFile $grammarFile): string
    {
        $baseFile = <<<PHP
            declare(strict_types = 1);
            
            final class %classname%Grammar {
                %methods%
            }
            PHP;

        return $baseFile;
    }

    /**
     * @param array<UseStatement> $uses
     */
    private function compileUses(array $uses): string
    {
        $uses = [];
        foreach ($uses as $use) {
            $uses[] = sprintf('use %s;', $use->name);
        }
        return implode("\n", $uses);
    }

    /**
     * @param array<GrammarDefinition> $rules
     */
    private function compileGrammarRules(array $rules): string
    {
        $definitions = [];
        foreach ($rules as $rule) {
            $expression = $this->compileSingularRule($rule);
            $definitions[] = trim("
            public static function {$rule->name}(): GrammarRule
            {
                return new GrammarRule('{$rule->name}')
                    $expression
                ;
            }
            ");
        }
        return implode("\n", $definitions);
    }

    private function compileSingularRule(GrammarDefinition $rule)
    {
        $seqs = [];
        foreach ($rule->rules as $sequence) {
            $tokens = [];
            foreach ($sequence->tokens as $token) {
                $tokens[] = $token->value;
            }
            $tokens = implode(',', $tokens);
            $seqs[] = trim("->is($tokens)->then(function (array \$m) {{$sequence->codeBlock}})");
        }
        return implode("\n", $seqs);
    }

}
