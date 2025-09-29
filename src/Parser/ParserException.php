<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

use RuntimeException;

/**
 * Represents an exception specific to parsing errors.
 *
 * This exception should be thrown when an error occurs during the parsing process,
 * typically indicating invalid input, unexpected behavior, or failure during parsing operations.
 */
class ParserException extends RuntimeException
{
}
