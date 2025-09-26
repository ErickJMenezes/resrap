<?php

declare(strict_types=1);

namespace Resrap\Component\Spec;

use UnitEnum;

/**
 * Represents a scanner interface responsible for tokenization operations.
 * Provides methods to retrieve the current token, access its value, and move
 * to the next token in the sequence.
 */
interface ScannerInterface
{
    /**
     * Retrieves a token representation as an instance of UnitEnum or null.
     *
     * @return UnitEnum|int The token as a UnitEnum instance, or null if no token is available.
     */
    public function lex(): int|UnitEnum;

    /**
     * Retrieves the value.
     *
     * @return string|null Returns the value if available, or null otherwise.
     */
    public function value(): ?string;

    /**
     * Advances the state or position in the process.
     *
     * @return void This method does not return a value.
     */
    public function advance(): void;

    /**
     * Moves the cursor to the specified index.
     *
     * @param int $index The position to move to.
     *
     * @return void
     */
    public function goto(int $index): void;

    /**
     * Retrieves the current index.
     *
     * @return int The current index.
     */
    public function index(): int;

    /**
     * Checks if the end of the file has been reached.
     *
     * @return bool Returns true if the end of the file is reached, false otherwise.
     */
    public function eof(): bool;
}
