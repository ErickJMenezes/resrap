<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use Closure;
use RuntimeException;

final class PendingSequence
{
    private bool $closed = false;

    /**
     * @param Closure(Closure(array<array-key, string>): mixed): Parser $function
     */
    public function __construct(private readonly Closure $function) {}

    /**
     * @param Closure(array<array-key, string>): mixed $whenMatches
     *
     * @return Parser
     * @throws RuntimeException if the callback is already defined and cannot be redefined
     */
    public function then(Closure $whenMatches): Parser
    {
        if ($this->closed) {
            throw new RuntimeException("Cannot redefine the callback");
        }
        $this->closed = true;
        return ($this->function)($whenMatches);
    }

    public function pass(): Parser
    {
        return $this->then(fn(array $matches): array => $matches);
    }
}
