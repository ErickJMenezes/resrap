<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use Closure;
use UnitEnum;

/**
 * Represents a pattern with an associated handler for processing matches.
 *
 * A `Pattern` instance is defined by a string pattern and a handler responsible
 * for handling matches of the pattern. The handler can either be a `UnitEnum`
 * or a `Closure` that processes the matches and returns a `UnitEnum`.
 *
 * The provided pattern is used to identify matches, and the handler allows
 * custom logic to be executed based on those matches.
 */
final readonly class Pattern
{
    public string $pattern;

    public Closure $handler;

    /**
     * @param string                                        $pattern Regex pattern to match.
     * @param (UnitEnum|(Closure(string&,array): UnitEnum)) $handler Handler to process matches.
     */
    public function __construct(
        string $pattern,
        UnitEnum|Closure $handler,
    ) {
        $this->pattern = $pattern;
        $this->handler = match (true) {
            $handler instanceof UnitEnum => fn(): UnitEnum => $handler,
            default => fn(string &$match, array $captureGroups): UnitEnum => $handler(
                $match,
                $captureGroups,
            ),
        };
    }
}
