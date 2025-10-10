<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

use RuntimeException;

class ScannerException extends RuntimeException
{
    public static function unexpectedCharacter(string $char, ?Position $at = null): self
    {
        $line = $at?->line ?? 0;
        $column = $at?->column ?? 0;
        return new self("Unexpected character \"{$char}\" at line {$line}:{$column}");
    }
}
