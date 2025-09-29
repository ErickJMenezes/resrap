<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

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
}
