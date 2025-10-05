<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use Resrap\Component\Grammar\Ast\GrammarDefinition;
use Resrap\Component\Grammar\Ast\GrammarFile;
use Resrap\Component\Grammar\Ast\RuleDefinition;
use Resrap\Component\Grammar\Ast\RuleToken;
use Resrap\Component\Grammar\Ast\UseStatement;
use Resrap\Component\Parser\GrammarRule;

final class GrammarSpecification
{
    public static function file(): GrammarRule
    {
        return new GrammarRule('grammar_file')
            ->is(
                self::className(...),
                self::optionalUseStatementList(...),
                self::start(...),
                self::listOfGrammarDefinitions(...),
            )
            ->then(fn(array $m) => new GrammarFile(
                $m[0],
                $m[1],
                $m[2],
                $m[3],
            ));
    }

    public static function start(): GrammarRule
    {
        return new GrammarRule('start')
            ->is(Token::START, Token::IDENTIFIER, Token::SEMICOLON)
            ->then(fn(array $m) => $m[1]);
    }

    public static function className(): GrammarRule
    {
        return new GrammarRule('class_name')
            ->is(Token::DEFINE_CLASSNAME, Token::IDENTIFIER, Token::SEMICOLON)
            ->then(fn(array $m) => $m[1]);
    }

    public static function qualifiedIdentifier(): GrammarRule
    {
        return new GrammarRule('qualified_identifier')
            ->is(Token::IDENTIFIER)
            ->then(fn(array $m) => $m[0])
            ->is(Token::IDENTIFIER, Token::BACKSLASH, self::qualifiedIdentifier(...))
            ->then(fn(array $m) => "{$m[0]}\\{$m[2]}");
    }

    public static function useStatement(): GrammarRule
    {
        return new GrammarRule('use_statement')
            ->is(Token::USE, self::qualifiedIdentifier(...), Token::SEMICOLON)
            ->then(fn(array $m) => new UseStatement($m[1]));
    }

    public static function useStatementList(): GrammarRule
    {
        return new GrammarRule('use_statement_list')
            ->is(self::useStatement(...))
            ->then(fn(array $m) => [$m[0]])
            ->is(self::useStatement(...), self::useStatementList(...))
            ->then(fn(array $m) => [$m[0], ...$m[1]]);
    }

    public static function optionalUseStatementList(): GrammarRule
    {
        return new GrammarRule('optional_use_statement_list')
            ->is()
            ->then(fn(array $m) => [])
            ->is(self::useStatementList(...))
            ->then(fn(array $m) => $m[0]);
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
                Token::IDENTIFIER,
                Token::ASSIGN,
                self::listOfMultipleRulesDefinitions(...),
                Token::SEMICOLON,
            )
            ->then(fn(array $m) => new GrammarDefinition(
                $m[0],
                $m[2],
            ));
    }

    public static function ruleIdentifier(): GrammarRule
    {
        return new GrammarRule('rule_identifier')
            ->is(self::qualifiedIdentifier(...), Token::STATIC_ACCESS, Token::IDENTIFIER)
            ->then(fn(array $m) => new RuleToken("{$m[0]}::{$m[2]}", RuleToken::TOK))
            ->is(Token::IDENTIFIER)
            ->then(fn(array $m) => new RuleToken($m[0], RuleToken::EXPR))
            ->is(Token::CHAR)
            ->then(fn(array $m) => new RuleToken($m[0], RuleToken::LITERAL))
            ->is(Token::STRING)
            ->then(fn(array $m) => new RuleToken($m[0], RuleToken::LITERAL));
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
            ->is(self::ruleDefinitionList(...), Token::CODE_BLOCK)
            ->then(fn(array $m) => new RuleDefinition(
                $m[0],
                trim($m[1]),
            ));
    }

    public static function listOfMultipleRulesDefinitions(): GrammarRule
    {
        return new GrammarRule('list_of_multiple_rules_definitions')
            ->is(self::ruleDefinition(...))
            ->then(fn(array $m) => [$m[0]])
            ->is(self::ruleDefinition(...), Token::PIPE, self::listOfMultipleRulesDefinitions(...))
            ->then(fn(array $m) => [$m[0], ...$m[2]]);
    }
}
