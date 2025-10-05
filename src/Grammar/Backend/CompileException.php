<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Backend;

use RuntimeException;
use Throwable;

class CompileException extends RuntimeException
{
    public static function atLeastOneRuleExpected(): self
    {
        return new self('The grammar must have at least one rule.');
    }

    public static function invalidCallbackCode(): self
    {
        return new self('Invalid callback code.');
    }

    public static function failedToCompileCallback(Throwable $previous, string $code): self
    {
        return new self("Failed to compile callback: {$previous->getMessage()}\nCode: $code", 0, $previous);
    }

    public static function invalidStartingPoint(string $startingPoint): self
    {
        return new self("Invalid starting point: {$startingPoint}");
    }
}
