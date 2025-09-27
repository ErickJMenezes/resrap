<?php

use Resrap\Component\Combinator\Parser;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\ScannerToken;

require __DIR__.'/../../vendor/autoload.php';

enum Token { case NUMBER; case PLUS; case MINUS; }

$scannerIt = new ScannerBuilder(
// skip whitespace
    new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
    // tokens
    new Pattern('{NUMBER}', Token::NUMBER),
    new Pattern('\+', Token::PLUS),
    new Pattern('-', Token::MINUS),
)
    ->aliases([
        'NUMBER' => '[0-9]+',
    ])
    ->build("12 + 34 - 5");

$number = fn() => Parser::is(Token::NUMBER)
    ->then(fn(array $m) => intval($m[0]));

$operator = fn() => Parser::is(Token::PLUS)
    ->then(fn(array $m) => $m[0])
    ->or(Token::MINUS)->then(fn(array $m) => $m[0]);

$expr = function () use ($number, $operator, &$expr) {
    return Parser::is($number)
        ->then(fn(array $m) => $m[0])
        ->or($number, $operator, $expr)
        ->then(fn(array $m) => [$m[0], $m[1], $m[2]]);
};

foreach ($scannerIt as $key => $token) {
    echo $key.': '.$token->name."\n";
}
$scannerIt->rewind();

$ast = $expr()->apply($scannerIt);

var_dump($ast);
