<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Backend;

use Closure;
use Resrap\Component\Grammar\Ast\GrammarDefinition;
use Resrap\Component\Grammar\Ast\GrammarFile;
use Resrap\Component\Grammar\Ast\RuleDefinition;
use Resrap\Component\Grammar\Ast\RuleToken;
use Resrap\Component\Parser\GrammarRule;
use UnitEnum;

/**
 * Class GrammarCompiler.
 *
 * @template-implements CompilerBackendInterface<GrammarFile>
 */
final class GrammarCompiler implements CompilerBackendInterface
{
    /**
     * @var array<string, GrammarRule>
     */
    private array $rules = [];

    private array $uses = [];

    public function compile(GrammarFile $ast)
    {
        if (count($ast->grammarDefinitions) < 1) {
            throw CompileException::atLeastOneRuleExpected();
        }

        foreach ($ast->grammarDefinitions as $grammarDefinition) {
            $this->rules[$grammarDefinition->name] = new GrammarRule($grammarDefinition->name);
        }

        if (!array_key_exists($ast->start, $this->rules)) {
            throw CompileException::invalidStartingPoint($ast->start);
        }

        foreach ($ast->uses as $use) {
            if ($use->alias !== null) {
                $this->uses[$use->alias] = $use->name;
                continue;
            }
            $parts = explode('\\', $use->name);
            $this->uses[$parts[count($parts) - 1]] = $use->name;
        }

        foreach ($ast->grammarDefinitions as $grammarDefinition) {
            $this->compileGrammarDefinitions($grammarDefinition);
        }

        return $this->rules[$ast->start];
    }

    private function resolveToken(RuleToken $token): GrammarRule|UnitEnum|string
    {
        if ($token->kind === RuleToken::EXPR) {
            return $this->rules[$token->value];
        }
        if ($token->kind === RuleToken::LITERAL) {
            return "$token->value";
        }
        [$basename, $value] = explode('::', $token->value);
        $enumName = $this->uses[$basename] ?? $basename;
        return constant("$enumName::$value");
    }

    private function compileGrammarDefinitions(GrammarDefinition $grammarDefinition): void
    {
        $rule = $this->rules[$grammarDefinition->name];

        foreach ($grammarDefinition->rules as $ruleDefinition) {
            $this->compileRuleAlternative($rule, $ruleDefinition);
        }
    }

    private function compileRuleAlternative(
        GrammarRule $rule,
        RuleDefinition $ruleDefinition,
    ): void
    {
        $symbols = $this->resolveSymbols($ruleDefinition->tokens);

        $callback = $this->compileCallback($ruleDefinition->codeBlock);

        $rule->is(...$symbols)->then($callback);
    }

    /**
     * @param array<RuleToken> $tokens
     *
     * @return array<string, GrammarRule|string>
     */
    private function resolveSymbols(array $tokens): array
    {
        $result = [];
        foreach ($tokens as $token) {
            $result[] = $this->resolveToken($token);
        }
        return $result;
    }

    private function compileCallback(string $codeBlock): Closure
    {
        $code = trim($codeBlock);
        $closureCode = "function (array \$m) { $code }";
        return fn() => $closureCode;
    }

    public function getUses(): array
    {
        return $this->uses;
    }
}
