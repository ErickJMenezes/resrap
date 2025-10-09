<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\Scanner;
use Resrap\Component\Scanner\ScannerToken;
use Resrap\Component\Scanner\StatefulScannerBuilder;
use Resrap\Component\Scanner\StateTransition;

final class GrammarScanner
{
    public static function create(): Scanner
    {
        return new StatefulScannerBuilder()
            ->state(
                name: 'grammar',
                patterns: [
                    // Assignment
                    new Pattern(':=', Token::ASSIGN),

                    // Operators
                    new Pattern('\|', Token::PIPE),

                    // Rule terminator
                    new Pattern(';', Token::SEMICOLON),

                    // String literal
                    new Pattern('"([^"\\\\]|\\\\.)*"', function (string &$value) {
                        // Strip quotes and unescape
                        $value = stripcslashes(substr($value, 1, -1));
                        return Token::STRING;
                    }),

                    // Char literal
                    new Pattern("'([^'\\\\]|\\\\.)*'", function (string &$value) {
                        $value = stripcslashes(substr($value, 1, -1));
                        return Token::CHAR;
                    }),

                    // AS
                    new Pattern('as', Token::AS),

                    // Identifiers
                    new Pattern('{IDENTIFIER}', Token::IDENTIFIER),

                    // Code blocks (semantic actions) → everything inside `{ ... }`
                    // NOTE: This should come *after* structural `{` so it's only used
                    // in the right parsing context.
                    new Pattern("\{((?:[^{}\"'\/]|\"(?:[^\"\\\\]|\\\\.)*\"|'(?:[^'\\\\]|\\\\.)*'|\/\/[^\n]*|\/\*(?:[^*]|\*(?!\/))*\*\/|(?R))*)\}", function (string &$value, array $groups) {
                        // strip outer braces and whitespaces
                        $value = trim($groups[0]);
                        // replace `$(number)` with `$m[$(number) - 1]` to convert to valid php code.
                        $value = preg_replace_callback(
                            '/\$(\d*)/',
                            fn(array $matches) => '$m[' . ((int)$matches[1] - 1) . ']',
                            $value,
                        );
                        return Token::CODE_BLOCK;
                    }),

                    new Pattern('\\\\', Token::BACKSLASH),

                    // Classnames
                    new Pattern('(?:%classname|%class)', Token::DEFINE_CLASSNAME),

                    // Start
                    new Pattern('%start', Token::START),

                    // Use
                    new Pattern('(?:%use|%import)', Token::USE),

                    // Static access
                    new Pattern('\:\:', Token::STATIC_ACCESS),

                    // Single-line comments
                    new Pattern('\/\/[^\n]*', ScannerToken::SKIP),

                    // Multi-line comments
                    new Pattern('\/\*.*?\*\/', ScannerToken::SKIP),

                    // Whitespace
                    new Pattern('{WS}', ScannerToken::SKIP),
                ],
                transitions: [],
            )
            ->aliases([
                'IDENTIFIER' => '[a-zA-Z_][a-zA-Z0-9_]*',
                'WS' => '[\s\t\n\r]+',
            ])
            ->build();
    }
}
