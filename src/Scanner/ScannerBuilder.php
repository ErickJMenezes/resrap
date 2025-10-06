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
        foreach ($aliases as $alias => $pattern) {
            $this->aliases['{'.$alias.'}'] = $pattern;
        }
        return $this;
    }

    public function build(): Scanner
    {
        return new RegexScanner($this->preparePatterns());
    }

    private function preparePatterns(): array
    {
        return new PatternBuilder($this->matchers, $this->aliases)
            ->build();
    }
}
