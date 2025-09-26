<?php

declare(strict_types = 1);

namespace Resrap\Component\Impl;

use Closure;
use RuntimeException;

final class PendingSequence
{
    private bool $closed = false;

    /**
     * @param Closure(Closure(array<array-key, string>): mixed): Combinator $function
     */
    public function __construct(private readonly Closure $function) {}

    /**
     * @param Closure(array<array-key, string>): mixed $whenMatches
     *
     * @return Combinator
     * @throws RuntimeException if the callback is already defined and cannot be redefined
     */
    public function then(Closure $whenMatches): Combinator
    {
        if ($this->closed) {
            throw new RuntimeException("Cannot redefine the callback");
        }
        $this->closed = true;
        return ($this->function)($whenMatches);
    }

    public function pass(): Combinator
    {
        return $this->then(fn(array $matches): array => $matches);
    }
}
