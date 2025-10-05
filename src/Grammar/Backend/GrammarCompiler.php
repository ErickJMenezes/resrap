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

    /**
     * @var array<string>
     */
    private array $closuresCode = [];

    private array $uses = [];

    public function compile(GrammarFile $ast)
    {
        if (count($ast->grammarDefinitions) < 1) {
            throw CompileException::atLeastOneRuleExpected();
        }

        foreach ($ast->grammarDefinitions as $grammarDefinition) {
            $this->rules[$grammarDefinition->name] = new GrammarRule($grammarDefinition->name);
        }

        foreach ($ast->uses as $use) {
            $parts = explode('\\', $use->name);
            $this->uses[$parts[count($parts) - 1]] = $use->name;
        }

        foreach ($ast->grammarDefinitions as $grammarDefinition) {
            $this->compileGrammarDefinitions($grammarDefinition);
        }

        return $this->rules[$ast->grammarDefinitions[0]->name];
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

        [$callback, $code] = $this->compileCallback($ruleDefinition->codeBlock);
        $this->closuresCode[] = $code;

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

    /**
     * @return array{Closure,string}
     * @throws CompileException
     */
    private function compileCallback(string $codeBlock): array
    {
        $code = trim($codeBlock);
        $closureCode = "static function (array \$m) { $code }";

        try {
            $closure = eval("return $closureCode;");

            if (!$closure instanceof Closure) {
                throw CompileException::invalidCallbackCode();
            }

            return [$closure, $closureCode];
        } catch (\Throwable $e) {
            throw CompileException::failedToCompileCallback($e, $code);
        }
    }

    public function getClosures(): array
    {
        return $this->closuresCode;
    }

    public function getUses(): array
    {
        return array_values($this->uses);
    }
}
