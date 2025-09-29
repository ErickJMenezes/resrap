<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

use Closure;
use RuntimeException;

final class PendingSequence
{
    private bool $closed = false;

    /**
     * @param Closure(Closure(array<array-key, string>): mixed): GrammarRule $function
     */
    public function __construct(private readonly Closure $function) {}

    /**
     * @param Closure(array<array-key, string>): mixed $whenMatches
     *
     * @return GrammarRule
     * @throws RuntimeException if the callback is already defined and cannot be redefined
     */
    public function then(Closure $whenMatches): GrammarRule
    {
        if ($this->closed) {
            throw new RuntimeException("Cannot redefine the callback");
        }
        $this->closed = true;
        return ($this->function)($whenMatches);
    }

    public function pass(): GrammarRule
    {
        return $this->then(fn(array $matches): array => $matches);
    }
}
