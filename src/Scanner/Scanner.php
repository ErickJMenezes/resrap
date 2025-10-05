<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

use UnitEnum;

/**
 * Represents a scanner interface responsible for tokenization operations.
 * Provides methods to retrieve the current token, access its value, and move
 * to the next token in the sequence.
 */
interface Scanner
{
    /**
     * Retrieves parses the next token and return in a representation of an instance of UnitEnum or int.
     *
     * When the return value is an int, it can be zero, which represents the end of the token stream; or it can be a
     * literal character {@see ord()} value.
     *
     * @return UnitEnum The token as a UnitEnum instance.
     */
    public function lex(): UnitEnum;

    /**
     * Retrieves the string value represented by the current token.
     *
     * @return string|null Returns the value if available, or null otherwise.
     */
    public function value(): ?string;

    /**
     * Sets the input string to be processed or used.
     *
     * @param string $input The input value to be set.
     *
     * @return void
     */
    public function setInput(string $input): void;

    /**
     * Returns the position of the previous token returned by the scanner.
     *
     * @return Position
     */
    public function lastTokenPosition(): Position;
}
