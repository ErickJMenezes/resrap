<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use Resrap\Component\Scanner\ManualPattern;
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
                    new ManualPattern(self::scanPhpCodeBlock(...)),

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

    private static function scanPhpCodeBlock(string $content): ?array
    {
        // checks if it starts with {
        if (!str_starts_with($content, '{')) {
            return null;  // Não é um code block
        }

        $position = 0;

        // jumps {
        $position++;

        $code = '';
        $depth = 1;
        $inString = false;
        $stringDelimiter = null;
        $escaped = false;
        $inComment = false;
        $commentType = null;

        while ($position < strlen($content) && $depth > 0) {
            $char = $content[$position];
            $code .= $char;
            $position++;

            // Handle escape
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\' && $inString) {
                $escaped = true;
                continue;
            }

            // Handle comments
            if (!$inString && !$inComment) {
                $nextChar = $position < strlen($content) ? $content[$position] : '';

                if ($char === '/' && $nextChar === '/') {
                    $inComment = true;
                    $commentType = 'line';
                    continue;
                }

                if ($char === '/' && $nextChar === '*') {
                    $inComment = true;
                    $commentType = 'block';
                    continue;
                }
            }

            if ($inComment) {
                if ($commentType === 'line' && $char === "\n") {
                    $inComment = false;
                } elseif ($commentType === 'block' && $char === '*') {
                    $nextChar = $position < strlen($content) ? $content[$position] : '';
                    if ($nextChar === '/') {
                        $code .= $nextChar;
                        $position++;
                        $inComment = false;
                    }
                }
                continue;
            }

            // Handle strings
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringDelimiter = $char;
                continue;
            }

            if ($inString && $char === $stringDelimiter) {
                $inString = false;
                $stringDelimiter = null;
                continue;
            }

            // Count braces (only outside strings and comments)
            if (!$inString && !$inComment) {
                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                }
            }
        }

        if ($depth !== 0) {
            // non matching closing brace
            return null;
        }

        // Remove last }
        $code = substr($code, 0, -1);

        // Process code
        $code = trim($code);
        $code = preg_replace_callback(
            '/\$(\d+)/',
            fn($m) => '$m[' . ((int)$m[1] - 1) . ']',
            $code
        );

        // returns: [token, bytesToConsume, value]
        // bytesToConsume = { + code + }
        return [Token::CODE_BLOCK, $position, $code];
    }
}
