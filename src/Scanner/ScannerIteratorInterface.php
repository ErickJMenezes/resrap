<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

use InvalidArgumentException;
use Iterator;
use UnitEnum;

/**
 * Extended iterator to be used by a parser.
 * Provides methods to retrieve the current token value and move to a specific index.
 *
 * @template-extends Iterator<int, UnitEnum|int>
 */
interface ScannerIteratorInterface extends Iterator
{
    /**
     * Retrieves the string value.
     *
     * @return string|null The value or null if not set.
     */
    public function value(): ?string;

    /**
     * Moves to the specified index.
     *
     * @param int $index The target index to navigate to.
     *
     * @return void
     * @throws InvalidArgumentException If the specified index could not be accessed.
     */
    public function goto(int $index): void;

    /**
     * Returns the farthest token and its value.
     *
     * Useful when the parser finds a value that cannot be parsed and
     *
     * @return array{UnitEnum|int, string|null, int} Token, Value and position.
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    public function farthest(): array;
}
