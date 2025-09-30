<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf;

use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\ScannerInterface;
use Resrap\Component\Scanner\ScannerToken;

final class EbnfScanner
{
    public static function create(): ScannerInterface
    {
        return new ScannerBuilder(
        // Assignment
            new Pattern(':=', EbnfToken::ASSIGN),

            // Operators
            new Pattern('\|', EbnfToken::PIPE),

            // Rule terminator
            new Pattern(';', EbnfToken::SEMICOLON),

            // String literal
            new Pattern('"([^"\\\\]|\\\\.)*"', function (string &$value) {
                // Strip quotes and unescape
                $value = stripcslashes(substr($value, 1, -1));
                return EbnfToken::STRING;
            }),

            // Char literal
            new Pattern("'([^'\\\\]|\\\\.)*'", function (string &$value) {
                $value = stripcslashes(substr($value, 1, -1));
                return EbnfToken::CHAR;
            }),

            // Identifiers
            new Pattern('{IDENTIFIER}', EbnfToken::IDENTIFIER),

            // Code blocks (semantic actions) → everything inside `{ ... }`
            // NOTE: This should come *after* structural `{` so it's only used
            // in the right parsing context.
            new Pattern("\{([^}]*)\}", function (string &$value, array $groups) {
                // strip outer braces and whitespaces
                $value = trim($groups[0]);
                // replace `$(number)` with `$m[$(number) - 1]` to convert to valid php code.
                $value = preg_replace_callback(
                    '/\$(\d*)/',
                    fn(array $matches) => '$m[' . ((int)$matches[1] - 1) . ']',
                    $value,
                );
                return EbnfToken::CODE_BLOCK;
            }),

            // Classnames
            new Pattern('(?:%classname|%class)', EbnfToken::CLASSNAME),

            // Use
            new Pattern('(?:%use|%import)', EbnfToken::USE),

            // Static access
            new Pattern('\:\:', EbnfToken::STATIC_ACCESS),

            new Pattern('{QUALIFIED_IDENTIFIER}', EbnfToken::QUALIFIED_IDENTIFIER),

            // Single-line comments
            new Pattern('\/\/[^\n]*', ScannerToken::SKIP),

            // Multi-line comments
            new Pattern('/\*.*?\*/', ScannerToken::SKIP),

            // Whitespace
            new Pattern('{WS}', ScannerToken::SKIP),
        )
            ->aliases([
                'IDENTIFIER' => '[a-zA-Z_][a-zA-Z0-9_]*',
                'QUALIFIED_IDENTIFIER' => '(?:[\\\\]{0,1}[a-zA-Z_][a-zA-Z0-9_]*)+',
                'WS' => '[\s\t\n\r]+',
            ])
            ->build();
    }
}
