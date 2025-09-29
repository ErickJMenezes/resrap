<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

/**
 * Represents the result of a parsing operation.
 *
 * This class encapsulates whether the parsing was successful, the resulting value
 * if successful, or an error if it was not.
 * It provides factory methods to
 * generate instances for success or failure outcomes.
 */
final readonly class ParseResult
{
    private function __construct(
        public bool $ok,
        public mixed $value,
        public ?ParseError $error,
    ) {}

    /**
     * Creates a successful result instance with the provided value.
     *
     * @param mixed $value The value to be encapsulated in the successful result.
     *
     * @return self A new instance representing a successful result.
     */
    public static function success(mixed $value): self {
        return new self(true, $value, null);
    }

    /**
     * Creates a new instance representing a failure.
     *
     * @param ParseError $error The parse error that caused the failure.
     *
     * @return self A new instance representing the failure state.
     */
    public static function failure(ParseError $error): self {
        return new self(false, null, $error);
    }
}
