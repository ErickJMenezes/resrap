<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

/**
 * Represents a builder for creating a scanner, allowing the configuration of pattern aliases
 * and the construction of a scanner capable of processing input based on predefined matchers.
 */
final class ScannerBuilder
{
    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * @var Pattern[]
     */
    private array $matchers;

    /**
     * @param array<Pattern> $matchers
     */
    public function __construct(Pattern ...$matchers)
    {
        $this->matchers = $matchers;
    }

    public function aliases(array $aliases): self
    {
        $this->aliases = $aliases;
        return $this;
    }

    public function build(): Scanner
    {
        // since this is a single state scanner, we can pass an empty array of transitions and states.
        return new StatefulScannerBuilder()
            ->aliases($this->aliases)
            ->state('main', $this->matchers, [])
            ->build();
    }
}
