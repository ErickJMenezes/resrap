<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf;

use Resrap\Component\Ebnf\Ast\GrammarDefinition;
use Resrap\Component\Ebnf\Ast\GrammarFile;
use Resrap\Component\Ebnf\Ast\GrammarRuleDefinition;
use Resrap\Component\Ebnf\Ast\RuleToken;
use Resrap\Component\Ebnf\Ast\UseStatement;
use Resrap\Component\Parser\GrammarRule;

final class EbnfGrammar
{
    public static function file(): GrammarRule
    {
        return new GrammarRule('grammar_file')
            ->is(EbnfToken::CLASSNAME, self::useList(...), self::listOfGrammarDefinitions(...))
            ->then(fn(array $m) => new GrammarFile(
                $m[0],
                $m[1],
                $m[2],
            ));
    }

    public static function useList(): GrammarRule
    {
        return new GrammarRule('use_list')
            ->is()
            ->then(fn(array $m) => [])
            ->is(EbnfToken::USE, self::useList(...))
            ->then(fn(array $m) => [
                new UseStatement($m[0]),
                ...$m[1],
            ]);
    }

    public static function listOfGrammarDefinitions(): GrammarRule
    {
        return new GrammarRule('list_of_grammar_definitions')
            ->is(self::grammarDefinition(...))
            ->then(fn(array $m) => [$m[0]])
            ->is(self::grammarDefinition(...), self::listOfGrammarDefinitions(...))
            ->then(fn(array $m) => [$m[0], ...$m[1]]);
    }

    public static function grammarDefinition(): GrammarRule
    {
        return new GrammarRule('grammar_definition')
            ->is(
                EbnfToken::IDENTIFIER,
                EbnfToken::ASSIGN,
                self::listOfMultipleRulesDefinitions(...),
                EbnfToken::SEMICOLON,
            )
            ->then(fn(array $m) => new GrammarDefinition(
                $m[0],
                $m[2],
            ));
    }

    public static function ruleIdentifier(): GrammarRule
    {
        return new GrammarRule('rule_identifier')
            ->is(EbnfToken::IDENTIFIER)
            ->then(fn(array $m) => new RuleToken($m[0], false))
            ->is(EbnfToken::CHAR)
            ->then(fn(array $m) => new RuleToken($m[0], true))
            ->is(EbnfToken::STRING)
            ->then(fn(array $m) => new RuleToken($m[0], true));
    }

    public static function ruleDefinitionList(): GrammarRule
    {
        return new GrammarRule('rule_definition_list')
            ->is(self::ruleIdentifier(...))
            ->then(fn(array $m) => [$m[0]])
            ->is(self::ruleIdentifier(...), self::ruleDefinitionList(...))
            ->then(fn(array $m) => [$m[0], ...$m[1]]);
    }

    public static function ruleDefinition(): GrammarRule
    {
        return new GrammarRule('rule_definition')
            ->is(self::ruleDefinitionList(...), EbnfToken::CODE_BLOCK)
            ->then(fn(array $m) => new GrammarRuleDefinition(
                $m[0],
                trim($m[1]),
            ));
    }

    public static function listOfMultipleRulesDefinitions(): GrammarRule
    {
        return new GrammarRule('list_of_multiple_rules_definitions')
            ->is(self::ruleDefinition(...))
            ->then(fn(array $m) => [$m[0]])
            ->is(self::ruleDefinition(...), EbnfToken::PIPE, self::listOfMultipleRulesDefinitions(...))
            ->then(fn(array $m) => [$m[0], ...$m[2]]);
    }
}
