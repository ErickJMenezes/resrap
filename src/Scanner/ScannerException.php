<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

use RuntimeException;

class ScannerException extends RuntimeException
{
    public static function unexpectedCharacter(string $char, null|string|int $at = null): self
    {
        return new self("Unexpected character \"{$char}\" at position {$at}.");
    }
}
