<?php

declare(strict_types=1);

namespace Resrap\Component\Spec;

use Resrap\Component\Impl\Combinator;
use Resrap\Component\Impl\PendingSequence;
use UnitEnum;
use Closure;

/**
 * Represents a combinator interface that provides methods to configure and process sequences.
 */
interface CombinatorInterface
{
    /**
     * Combines the given sequence elements using logical OR to create a pending sequence.
     *
     * @param (Closure(): Combinator|UnitEnum|string)|Combinator|UnitEnum|string ...$sequence The sequence of elements to combine.
     *
     * @return PendingSequence The resulting pending sequence after applying the OR operation.
     */
    public function or(Closure|Combinator|UnitEnum|string ...$sequence): PendingSequence;
}
