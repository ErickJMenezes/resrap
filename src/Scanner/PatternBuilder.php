<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

final class PatternBuilder
{
    /**
     * @param array<array-key, Pattern> $matchers
     * @param array<string, string>     $aliases
     */
    public function __construct(
        private array $matchers,
        private array $aliases,
    ) {}

    public function build(): array
    {
        $aliases = array_keys($this->aliases);
        $replacements = array_values($this->aliases);
        $patterns = [];
        foreach ($this->matchers as $matcher) {
            $currentPattern = str_replace(
                $aliases,
                $replacements,
                "^$matcher->pattern",
            );
            $patterns["/$currentPattern/xs"] = $matcher->handler;
        }
        return $patterns;
    }
}
