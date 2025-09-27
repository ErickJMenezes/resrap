<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

final class ScannerBuilder
{
    /**
     * @var array<string, array{string, string}>
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
            $this->aliases['{'.$alias.'}'] = ["(?&$alias)", "(?<$alias> $pattern)"];
        }
        return $this;
    }

    public function build(string $input): ScannerIterator
    {
        return new ScannerIterator(new RegexScanner($this->preparePatterns(), $input));
    }

    private function preparePatterns(): array
    {
        $patterns = [];
        foreach ($this->matchers as $matcher) {
            $currentPattern = "^$matcher->pattern";
            $currentPatternSubroutines = [];
            foreach ($this->aliases as $alias => [$replacement, $subroutineDefinition]) {
                if (str_contains($matcher->pattern, $alias)) {
                    $currentPattern = str_replace($alias, $replacement, $currentPattern);
                    $currentPatternSubroutines[] = $subroutineDefinition;
                }
            }
            if (count($currentPatternSubroutines) > 0) {
                $currentPattern = "(?(DEFINE)\n".implode("\n", $currentPatternSubroutines).") $currentPattern";
            }
            $patterns["/$currentPattern/xs"] = $matcher->handler;
        }
        return $patterns;
    }
}
