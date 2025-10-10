<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use LogicException;

/**
 * A builder class for constructing a stateful scanner. This facilitates the
 * definition of states, associated patterns, transitions, and the establishment
 * of an initial state, ultimately resulting in the creation of a scanner.
 */
final class StatefulScannerBuilder
{
    /**
     * @var State[]
     */
    private array $states = [];

    private ?string $initialStateName = null;

    /**
     * @var array<string, string>
     */
    private array $patternAliases = [];

    /**
     * @var array<string, array{patterns: Pattern[], transitions: StateTransition[]}>
     */
    private array $rawStates = [];

    /**
     * Define a new state.
     *
     * @param non-empty-string                        $name
     * @param array<array-key, Pattern|ManualPattern> $patterns
     * @param StateTransition[]                       $transitions
     *
     * @return $this
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    public function state(string $name, array $patterns, array $transitions): self
    {
        if (count($patterns) === 0) {
            throw new LogicException('At least one pattern must be defined.');
        }
        $this->rawStates[$name] = [
            'patterns' => $patterns,
            'transitions' => $transitions,
        ];

        return $this;
    }

    /**
     * @param array<string, string> $aliases
     *
     * @return $this
     */
    public function aliases(array $aliases): self
    {
        $this->patternAliases = $aliases;

        return $this;
    }

    public function build(): Scanner
    {
        if (count($this->rawStates) === 0) {
            throw new LogicException('At least one state must be defined.');
        }
        $this->initialStateName ??= array_key_first($this->rawStates);
        if (!isset($this->rawStates[$this->initialStateName])) {
            throw new LogicException("There is no state named '{$this->initialStateName}'.");
        }
        $states = [];
        foreach ($this->rawStates as $name => $rawState) {
            $states[$name] = new State(
                name: $name,
                patterns: new PatternBuilder($rawState['patterns'], $this->patternAliases)->build(),
                transitions: $rawState['transitions'],
            );
        }
        $initialState = $states[$this->initialStateName];
        return new RegexScanner($initialState, $states);
    }

    public function setInitialState(string $name): self
    {
        $this->initialStateName = $name;
        return $this;
    }
}
