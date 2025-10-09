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
        $rawAliases = $this->prepareAliases();
        $aliases = array_keys($rawAliases);
        $replacements = array_values($rawAliases);
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

    private function prepareAliases(): array
    {
        $aliases = [];
        foreach ($this->aliases as $alias => $pattern) {
            $aliases['{'.$alias.'}'] = $pattern;
        }
        return $aliases;
    }
}
