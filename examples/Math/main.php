<?php

use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\Scanner;
use Resrap\Component\Scanner\ScannerToken;
use Resrap\Examples\Math\MathParser;
use Resrap\Examples\Math\MathToken;

require __DIR__.'/../../vendor/autoload.php';

function scanner(): Scanner
{
    return new ScannerBuilder(
        // skip whitespace
        new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
        // tokens
        new Pattern('{NUMBER}', MathToken::NUMBER),
        new Pattern('\+', MathToken::PLUS),
        new Pattern('-', MathToken::MINUS),
        new Pattern('\*', MathToken::TIMES),
        new Pattern('\\/', MathToken::DIV),
    )
        ->aliases([
            'NUMBER' => '[0-9]+',
        ])
        ->build();
}

$parser = new MathParser(scanner());;

var_dump($parser->parse('3 + 3 * 2 / 2'));
