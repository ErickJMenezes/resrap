<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

use Resrap\Component\Scanner\Position;
use RuntimeException;
use UnitEnum;

/**
 * Represents an exception specific to parsing errors.
 *
 * This exception should be thrown when an error occurs during the parsing process,
 * typically indicating invalid input, unexpected behavior, or failure during parsing operations.
 */
class ParserException extends RuntimeException
{
    public static function expectedEof(UnitEnum $token, int $position): self
    {
        return new self("Unexpected token: {$token->name} at position {$position}. Expected EOF.");
    }

    public static function syntaxError(array $expected, string $actual, int $position): self
    {
        $exp = implode(", ", $expected);
        $msg = "Parse error at position {$position}: expected {$exp}, found '{$actual}'";
        return new self($msg);
    }

    public static function unexpectedToken(UnitEnum $token): self
    {
        return new self("Unexpected token: {$token->name}.");
    }

    public static function invalidSyntax(
        string $input,
        Position $position,
        string $unexpected,
        string $unexpectedValue,
        array $expectedTokens
    ): self
    {
        $expected = empty($expectedTokens)
            ? 'end of input'
            : implode(', ', $expectedTokens);

        $lines = explode("\n", $input);
        $errorLine = $lines[$position->line - 1] ?? '';

        // Mostra linha anterior e posterior para contexto
        $prevLine = $lines[$position->line - 2] ?? null;
        $nextLine = $lines[$position->line] ?? null;

        $context = '';
        if ($prevLine !== null) {
            $context .= sprintf("%4d | %s\n", $position->line - 1, $prevLine);
        }
        $context .= sprintf("%4d | %s\n", $position->line, $errorLine);
        $context .= sprintf("     | %s\n", str_repeat(' ', $position->column - 1) . '^');
        if ($nextLine !== null) {
            $context .= sprintf("%4d | %s\n", $position->line + 1, $nextLine);
        }

        $msg = sprintf(
            "Syntax error at line %d, column %d:\n\n%s\nUnexpected token \"%s\" (value: \"%s\").\nExpected: %s",
            $position->line,
            $position->column,
            $context,
            $unexpected,
            $unexpectedValue,
            $expected
        );
        return new self($msg);
    }
}
