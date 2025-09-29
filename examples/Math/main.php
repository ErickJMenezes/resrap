<?php

use Resrap\Component\Parser\Parser;
use Resrap\Component\Parser\GrammarRule;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\ScannerInterface;
use Resrap\Component\Scanner\ScannerToken;

require __DIR__.'/../../vendor/autoload.php';

enum Token
{
    case NUMBER;
    case PLUS;
    case MINUS;
    case TIMES;
    case DIV;
}

function scanner(): ScannerInterface
{
    return new ScannerBuilder(
    // skip whitespace
        new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
        // tokens
        new Pattern('{NUMBER}', Token::NUMBER),
        new Pattern('\+', Token::PLUS),
        new Pattern('-', Token::MINUS),
        new Pattern('\*', Token::TIMES),
        new Pattern('\\/', Token::DIV),
    )
        ->aliases([
            'NUMBER' => '[0-9]+',
        ])
        ->build();
}

function grammar(): GrammarRule
{
    // In our grammar, to match the number is as simple as matching a token.
    $number = new GrammarRule('number')
        ->is(Token::NUMBER)
        // Then, when we match a number, we convert it to an integer.
        // The position zero [0] is the first matched token.
        ->then(fn(array $m) => intval($m[0]));

    // Same as matching a number, but we match an operator.
    $operator = new GrammarRule('operator')
        ->is(Token::PLUS)
        ->then(fn(array $m) => $m[0])
        ->is(Token::MINUS)
        ->then(fn(array $m) => $m[0])
        ->is(Token::TIMES)
        ->then(fn(array $m) => $m[0])
        ->is(Token::DIV)
        ->then(fn(array $m) => $m[0]);

    // The expression is a number or a number followed by an operator followed by an expression.
    $expression = new GrammarRule('expression')
        ->is($number)
        ->then(fn(array $m) => $m[0]);
    $expression
        ->is($number, $operator, $expression)
        ->then(fn(array $m) => "{$m[0]} {$m[1]} {$m[2]}");

    // Finally, we return the calculator parser, evaluating our math expression.
    return new GrammarRule("calculator")
        ->is($expression)
        ->then(fn(array $m) => eval("return {$m[0]};"));
}

$parser = new Parser(scanner(), grammar());

var_dump($parser->parse('3 + 3 * 2 / 2'));
