<?php

use Pest\Expectation;
use Resrap\Component\Grammar\Ast\GrammarFile;
use Resrap\Component\Grammar\GrammarParser;
use Resrap\Component\Parser\ParserException;
use Resrap\Component\Scanner\Position;

test('must parse a grammar file', function () {
    $text = '
    %class MyGrammar;
    
    %use Add\\More;
    %use Add;
    
    %start expr;

    expr := expr \'+\' Add::More { return new Add($1, $3); }
          | term            { return $1; }
          ;';
    $parser = new GrammarParser();
    $result = $parser->parse($text);

    expect($result)
        ->toBeInstanceOf(GrammarFile::class)
        ->classname
        ->toBe('MyGrammar')
        ->uses
        ->toHaveCount(2)
        ->sequence(
            fn(Expectation $use) => $use->name->toBe('Add\\More'),
            fn(Expectation $use) => $use->name->toBe('Add'),
        )
        ->grammarDefinitions
        ->toHaveCount(1)
        ->sequence(
            fn(Expectation $grammar) => $grammar
                ->name->toBe('expr')
                ->rules->toHaveCount(2)
                ->sequence(
                    fn(Expectation $rule) => $rule
                        ->tokens->toHaveCount(3)
                        ->codeBlock->toBe('return new Add($m[0], $m[2]);'),
                    fn(Expectation $rule) => $rule
                        ->tokens->toHaveCount(1)
                        ->codeBlock->toBe('return $m[0];'),
                ),
        );
});

test('must parse a grammar file without uses', function () {
    $text = '
    %class MyGrammar;
    %start expr;

    expr := expr \'+\' term { return new Add($1, $3); }
          | term            { return $1; }
          ;';
    $parser = new GrammarParser();
    $result = $parser->parse($text);

    expect($result)
        ->toBeInstanceOf(GrammarFile::class)
        ->classname
        ->toBe('MyGrammar')
        ->uses
        ->toHaveCount(0)
        ->grammarDefinitions
        ->toHaveCount(1)
        ->sequence(
            fn(Expectation $grammar) => $grammar
                ->name->toBe('expr')
                ->rules->toHaveCount(2)
                ->sequence(
                    fn(Expectation $rule) => $rule
                        ->tokens->toHaveCount(3)
                        ->codeBlock->toBe('return new Add($m[0], $m[2]);'),
                    fn(Expectation $rule) => $rule
                        ->tokens->toHaveCount(1)
                        ->codeBlock->toBe('return $m[0];'),
                ),
        );
});

test('must throw and error when it founds invalid grammar', function () {
    $text = 'expr := expr \'+\' term // without code block
                   | term            { return $1; }
                   ;';
    $parser = new GrammarParser();
    expect(fn() => $parser->parse($text))
        ->toThrow(ParserException::invalidSyntax(
            $text,
            new Position(0,1, 1),
            'IDENTIFIER',
            'expr',
            ['DEFINE_CLASSNAME']
        ));
});

test('must parse empty production', function () {
    $text = '
    %class MyGrammar;
    %start expr;
    expr := expr "+" term { return new Add($1, $3); }
          |               { return $1; }
          ;
    ';

    $parser = new GrammarParser();
    $result = $parser->parse($text);
    expect($result)
        ->toBeInstanceOf(GrammarFile::class)
        ->classname
        ->toBe('MyGrammar')
        ->uses
        ->toHaveCount(0)
        ->grammarDefinitions
        ->toHaveCount(1)
        ->sequence(
            fn(Expectation $grammar) => $grammar
                ->rules->toHaveCount(2)
                ->sequence(
                    fn(Expectation $grammar) => $grammar
                        ->tokens->toHaveCount(3),
                    fn(Expectation $grammar) => $grammar
                        ->tokens->toHaveCount(0),
                ),
        );
});
