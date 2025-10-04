<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

use Closure;
use UnitEnum;

/**
 * The Table class is responsible for generating the necessary parsing tables,
 * including states, transitions, action, and goto tables, for a grammar-based
 * analysis process. It computes the FIRST and FOLLOW sets essential for grammar parsing.
 */
final class Table
{
    public const int SHIFT = 0;

    public const int REDUCE = 1;

    public const int ACCEPT = 2;

    public const int ERROR = 3;

    private(set) array $states;

    private(set) array $callbackTable = [];

    private array $transitions;

    private(set) array $actionTable = [];

    private(set) array $gotoTable = [];

    private array $first;

    private array $follow;

    private(set) array $rules;

    public function __construct(GrammarRule $root)
    {
        $this->rules = $rules = $this->buildRules($root);
        $items = $this->buildItems($rules);
        [$this->states, $this->transitions] = $this->generateStates($items, $rules);

        // Calculate FIRST e FOLLOW
        $this->first = $this->computeFirst($rules);
        $this->follow = $this->computeFollow($rules);

        // Build ACTION and GOTO tables
        $this->buildActionAndGotoTables($rules);
    }

    private function buildRules(GrammarRule $root): array
    {
        $ruleIsParsed = [];
        $pendingStack = [$root];
        $result = [];
        while (count($pendingStack) !== 0) {
            $ruleSet = array_shift($pendingStack);
            if (isset($ruleIsParsed[$ruleSet->name])) {
                continue;
            }
            $ruleIsParsed[$ruleSet->name] = true;
            foreach ($ruleSet->combinations as $combinationIndex => $rules) {
                $items = [];
                foreach ($rules as $rule) {
                    if ($rule instanceof Closure) {
                        $rule = $rule();
                    }
                    if ($rule instanceof GrammarRule) {
                        $pendingStack[] = $rule;
                    }
                    $items[] = $this->translate($rule);
                }
                $result[] = [$this->translate($ruleSet), $items];
                $this->callbackTable[] = $ruleSet->callbacks[$combinationIndex];
            }
        }
        return $result;
    }

    private function translate(string|UnitEnum|GrammarRule $rule): int|string
    {
        return match (true) {
            $rule instanceof GrammarRule => $rule->name,
            $rule instanceof UnitEnum => $rule->name,
            default => $rule,
        };
    }

    private function buildItems(array $rules): array
    {
        $items = [];
        foreach ($rules as $ruleIndex => [$lhs, $rhs]) {
            for ($i = 0; $i < count($rhs); $i++) {
                $items[] = [$ruleIndex, $lhs, $rhs, $i];
            }
        }
        return $items;
    }

    private function generateStates(array $items, array $rules): array
    {
        $states = [];
        $transitions = [];
        $stateQueue = [];

        $state0 = $this->closure([$items[0]], $rules);
        $states[] = $state0;
        $stateQueue[] = 0;

        while (count($stateQueue) !== 0) {
            $stateIndex = array_shift($stateQueue);
            $state = $states[$stateIndex];
            $symbols = $this->symbolsAfterDot($state);

            // create a transition
            foreach ($symbols as $symbol) {
                $nextState = $this->gotoState($state, $rules, $symbol);

                if (count($nextState) === 0) {
                    continue;
                }

                $nextStateIndex = array_search($nextState, $states, true);
                if ($nextStateIndex === false) {
                    $nextStateIndex = count($states);
                    $states[] = $nextState;
                    $stateQueue[] = $nextStateIndex;
                }
                $transitions[] = [$stateIndex, $symbol, $nextStateIndex];
            }
        }

        return [$states, $transitions];
    }

    private function closure(array $items, array $rules): array
    {
        $changed = true;
        $result = $items;
        while ($changed) {
            $changed = false;
            foreach ($result as [, , $rhs, $dot]) {
                if ($dot > count($rhs)) {
                    continue;
                }
                $symbol = $rhs[$dot] ?? null;
                if ($symbol === null) {
                    continue;
                }
                foreach ($rules as $ruleIndex => [$lhs, $ruleRhs]) {
                    if ($lhs === $symbol) {
                        $newItem = [$ruleIndex, $lhs, $ruleRhs, 0];
                        if (!in_array($newItem, $result, true)) {
                            $result[] = $newItem;
                            $changed = true;
                        }
                    }
                }
            }
        }
        return $result;
    }

    private function symbolsAfterDot(array $items): array
    {
        $symbols = [];
        foreach ($items as [, , $rhs, $dot]) {
            if ($dot < count($rhs)) {
                $symbols[] = $rhs[$dot];
            }
        }
        return array_unique($symbols);
    }

    private function gotoState(array $items, array $rules, string $symbol): array
    {
        $next = [];

        foreach ($items as [$ruleIndex, $lhs, $rhs, $dot]) {
            if ($dot < count($rhs) && $rhs[$dot] === $symbol) {
                $next[] = [
                    $ruleIndex,
                    $lhs,
                    $rhs,
                    $dot + 1,
                ];
            }
        }

        return $this->closure($next, $rules);
    }

    private function isTerminal(string|int $symbol): bool
    {
        // Terminais são strings em MAIÚSCULAS
        return is_string($symbol) && strtoupper($symbol) === $symbol;
    }

    private function canBeEmpty(string|int $symbol, array $rules): bool
    {
        // Verifica se existe uma regra A → ε (vazia)
        foreach ($rules as [$lhs, $rhs]) {
            if ($lhs === $symbol && count($rhs) === 0) {
                return true;
            }
        }
        return false;
    }

    private function computeFirst(array $rules): array
    {
        $first = [];

        // Initialize first for all symbols
        foreach ($rules as [$lhs, $rhs]) {
            if (!isset($first[$lhs])) {
                $first[$lhs] = [];
            }
            foreach ($rhs as $symbol) {
                if (!isset($first[$symbol])) {
                    $first[$symbol] = $this->isTerminal($symbol) ? [$symbol] : [];
                }
            }
        }

        // Iterate until converge
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($rules as [$lhs, $rhs]) {
                $sizeBefore = count($first[$lhs]);

                foreach ($rhs as $symbol) {
                    $first[$lhs] = array_unique(array_merge($first[$lhs], $first[$symbol] ?? []));

                    // If the symbol cannot be empty, stop.
                    if (!$this->canBeEmpty($symbol, $rules)) {
                        break;
                    }
                }

                if (count($first[$lhs]) > $sizeBefore) {
                    $changed = true;
                }
            }
        }

        return $first;
    }

    private function computeFollow(array $rules): array
    {
        $follow = [];

        // Initialize
        foreach ($rules as [$lhs,]) {
            $follow[$lhs] = [];
        }

        // FOLLOW of the initial symbol contains $
        $follow[$rules[0][0]] = ['$'];

        $changed = true;
        while ($changed) {
            $changed = false;

            foreach ($rules as [$lhs, $rhs]) {
                for ($i = 0; $i < count($rhs); $i++) {
                    $symbol = $rhs[$i];

                    if ($this->isTerminal($symbol)) {
                        continue;
                    }

                    if (!isset($follow[$symbol])) {
                        $follow[$symbol] = [];
                    }

                    $sizeBefore = count($follow[$symbol]);

                    // if next symbol, add FIRST to them
                    if ($i + 1 < count($rhs)) {
                        $nextSymbol = $rhs[$i + 1];
                        if ($this->isTerminal($nextSymbol)) {
                            $follow[$symbol][] = $nextSymbol;
                        } else {
                            $follow[$symbol] = array_merge(
                                $follow[$symbol],
                                $this->first[$nextSymbol] ?? [],
                            );
                        }
                        $follow[$symbol] = array_unique($follow[$symbol]);
                    }

                    // If is last symbol and the next can be empty
                    if ($i + 1 >= count($rhs)) {
                        $follow[$symbol] = array_unique(
                            array_merge(
                                $follow[$symbol],
                                $follow[$lhs],
                            ),
                        );
                    }

                    if (count($follow[$symbol]) > $sizeBefore) {
                        $changed = true;
                    }
                }
            }
        }

        return $follow;
    }

    private function findTransition(int $fromState, string|int $symbol): ?int
    {
        foreach ($this->transitions as [$from, $sym, $to]) {
            if ($from === $fromState && $sym === $symbol) {
                return $to;
            }
        }
        return null;
    }

    private function buildActionAndGotoTables(array $rules): void
    {
        foreach ($this->states as $stateIndex => $state) {
            $this->actionTable[$stateIndex] = [];
            $this->gotoTable[$stateIndex] = [];

            foreach ($state as [$ruleIndex, $lhs, $rhs, $dot]) {
                // Full item → REDUCE
                if ($dot >= count($rhs)) {
                    foreach ($this->follow[$lhs] ?? [] as $terminal) {
                        $this->actionTable[$stateIndex][$terminal] = [self::REDUCE, $ruleIndex];
                    }
                } // Dot before terminal → SHIFT
                elseif ($dot < count($rhs) && $this->isTerminal($rhs[$dot])) {
                    $terminal = $rhs[$dot];
                    $nextState = $this->findTransition($stateIndex, $terminal);
                    if ($nextState !== null) {
                        $this->actionTable[$stateIndex][$terminal] = [self::SHIFT, $nextState];
                    }
                }
            }

            foreach ($this->transitions as [$fromState, $symbol, $toState]) {
                if (!$this->isTerminal($symbol)) {
                    $this->gotoTable[$fromState][$symbol] = $toState;
                }
            }
        }

        // Accept state (state with the start item being complete)
        foreach ($this->states as $stateIndex => $state) {
            foreach ($state as [$ruleIndex, $lhs, $rhs, $dot]) {
                if ($ruleIndex === 0 && $dot >= count($rhs)) {
                    $this->actionTable[$stateIndex]['$'] = [self::ACCEPT, 0];
                    break 2;
                }
            }
        }
    }
}
