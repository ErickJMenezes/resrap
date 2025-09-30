<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

use Closure;
use UnitEnum;

/**
 * Represents a grammar rule that can define and match sequences of elements and apply callbacks.
 */
final class GrammarRule
{
    /**
     * @var array<array-key, array<int, (Closure(): (GrammarRule|UnitEnum|string))|GrammarRule|UnitEnum|string>>
     */
    private(set) array $combinations = [];

    /**
     * @var array<array-key, Closure(array<array-key, string>): mixed>
     */
    private(set) array $callbacks = [];

    /**
     * Initializes a new Parser instance with a given name.
     */
    public function __construct(public readonly string $name) {}

    /**
     * Combines a sequence of elements into a pending sequence.
     *
     * @param (Closure(): (GrammarRule|UnitEnum|string))|GrammarRule|UnitEnum|string ...$sequence The sequence of
     *                                                                               elements to combine.
     *
     * @return PendingSequence A new PendingSequence instance created with the provided sequence.
     */
    public function is(Closure|GrammarRule|UnitEnum|string ...$sequence): PendingSequence
    {
        $sequence = array_map(
            fn($item) => match (true) {
                $item instanceof Closure => fn(): GrammarRule|UnitEnum|string => $item(),
                default => $item,
            },
            $sequence,
        );
        return new PendingSequence(function (Closure $whenMatches) use (&$sequence): GrammarRule {
            $this->combinations[] = $sequence;
            $this->callbacks[] = $whenMatches;
            return $this;
        });
    }
}
