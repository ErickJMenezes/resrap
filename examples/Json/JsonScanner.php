<?php

declare(strict_types = 1);

namespace Resrap\Examples\Json;

use Resrap\Component\Combinator\ScannerIterator;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerToken;
use Resrap\Component\Scanner\ScannerBuilder;

final class JsonScanner
{
    public static function build(string $input): ScannerIterator
    {
        return new ScannerBuilder(
            new Pattern("\{", Token::LBRACE),
            new Pattern("\}", Token::RBRACE),
            new Pattern("\[", Token::LBRACKET),
            new Pattern("\]", Token::RBRACKET),
            new Pattern("\,", Token::COMMA),
            new Pattern("\:", Token::COLON),
            new Pattern("true", Token::TRUE),
            new Pattern("false", Token::FALSE),
            new Pattern("null", Token::NULL),
            new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
            new Pattern('{STRING}', function (string &$value) {
                $value = trim($value, '"');
                return Token::STRING;
            }),
            new Pattern('{NUMBER}', Token::NUMBER),
        )
            ->aliases([
                'STRING' => '\"([^\\"]|\["\/bfnrt]|\\\\u[0-9a-fA-F]{4})*\"',
                'NUMBER' => '-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?',
            ])
            ->build($input);
    }
}
