<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

use RuntimeException;
use UnitEnum;

class ParserException extends RuntimeException
{
    public static function noSuitableMatcherFound(int|UnitEnum $token, int $position): self
    {
        return new self("Unexpected token found at", previous: $previous);
    }
}
