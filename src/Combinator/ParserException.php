<?php

declare(strict_types=1);

namespace Resrap\Component\Combinator;

use RuntimeException;

class ParserException extends RuntimeException
{
    public static function noSuitableMatcherFound(?self $previous = null): self
    {
        return new self("No suitable matcher found for current token stream", previous: $previous);
    }
}
