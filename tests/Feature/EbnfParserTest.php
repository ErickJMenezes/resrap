<?php

use Pest\Expectation;
use Resrap\Component\Ebnf\Ast\GrammarFile;
use Resrap\Component\Ebnf\EbnfParser;
use Resrap\Component\Parser\ParserException;

test('must parse a grammar file', function () {
    $text = '
    %class MyGrammar

    %use Add\More
    %use Add

    expr := expr \'+\' term { return new Add($1, $3); }
          | term            { return $1; }
          ;';
    $parser = new EbnfParser();
    $result = $parser->parse($text);

    expect($result)
        ->toBeInstanceOf(GrammarFile::class)
        ->classname
        ->toBe('MyGrammar')
        ->uses
        ->toHaveCount(2)
        ->sequence(
            fn(Expectation $use) => $use->name->toBe('Add\More'),
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


test('must throw and error when it founds invalid grammar', function () {
    $text = 'expr := expr \'+\' term // without code block
                   | term            { return $1; }
                   ;';
    $parser = new EbnfParser();
    expect(fn() => $parser->parse($text))
        ->toThrow(ParserException::syntaxError(['CODE_BLOCK'], 'PIPE', 5));
});
