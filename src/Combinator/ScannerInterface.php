<?php

declare(strict_types=1);

namespace Resrap\Component\Combinator;

use UnitEnum;

/**
 * Represents a scanner interface responsible for tokenization operations.
 * Provides methods to retrieve the current token, access its value, and move
 * to the next token in the sequence.
 */
interface ScannerInterface
{
    /**
     * Retrieves parses the next token and return in a representation of an instance of UnitEnum or int.
     *
     * When the return value is an int, it can be zero, which represents the end of the token stream; or it can be a
     * literal character {@see ord()} value.
     *
     * @return UnitEnum|int The token as a UnitEnum instance, or int.
     */
    public function lex(): int|UnitEnum;

    /**
     * Retrieves the string value represented by the current token.
     *
     * @return string|null Returns the value if available, or null otherwise.
     */
    public function value(): ?string;
}
