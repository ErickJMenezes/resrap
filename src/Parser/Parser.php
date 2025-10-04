<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

use Resrap\Component\Scanner\ScannerInterface;

/**
 * LALR Parser
 */
final class Parser
{
    public function __construct(
        private array $actions,
        private array $goto,
        private array $callbacks,
        private array $rules,
        private readonly ScannerInterface $scanner,
    ) {}

    public static function fromGrammar(GrammarRule $root, ScannerInterface $scanner)
    {
        $table = new Table($root);
        return new self(
            $table->actionTable,
            $table->gotoTable,
            $table->callbackTable,
            $table->rules,
            $scanner
        );
    }

    public function parse(string $input): mixed
    {
        $this->scanner->setInput($input);
        $stateStack = [0];
        $valueStack = [];
        $token = null;
        $tokenName = null;

        while(true) {
            $currentState = end($stateStack);

            // Only takes a new item if the current is empty
            if ($token === null) {
                $token = $this->scanner->lex();
                $tokenName = $token->name;

                // Map EOF to $ (end symbol in tables)
                if ($tokenName === 'EOF') {
                    $tokenName = '$';
                }
            }

            $action = $this->actions[$currentState][$tokenName] ?? null;
            if ($action === null) {
                // Coleta tokens esperados neste estado
                $expectedTokens = array_keys($this->actions[$currentState] ?? []);

                throw ParserException::invalidSyntax(
                    $input,
                    $this->scanner->lastTokenPosition(),
                    $token->name,
                    $this->scanner->value() ?? $tokenName,
                    $expectedTokens,
                );
            }

            [$actionType, $actionValue] = $action;

            switch ($actionType) {
                case Table::SHIFT:
                    $valueStack[] = $this->scanner->value();
                    $stateStack[] = $actionValue;
                    $token = null; // Consume token
                    break;
                case Table::REDUCE:
                    $ruleIndex = $actionValue;
                    $callback = $this->callbacks[$ruleIndex];
                    $ruleLength = $this->getRuleLength($ruleIndex);

                    // remove symbols from stack
                    $args = array_splice($valueStack, -$ruleLength);
                    array_splice($stateStack, -$ruleLength);

                    // execute callback
                    $callbackResult = $callback($args);
                    $valueStack[] = $callbackResult;

                    // check goto
                    $currentState = end($stateStack);
                    $ruleNonTerminal = $this->getRuleNonTerminal($ruleIndex);
                    $nextState = $this->goto[$currentState][$ruleNonTerminal];
                    $stateStack[] = $nextState;
                    break;
                case Table::ACCEPT:
                    $callback = $this->callbacks[0];
                    return $callback($valueStack);
            }
        }
    }

    private function getRuleLength(int $ruleIndex): int
    {
        return count($this->rules[$ruleIndex][1]);
    }

    private function getRuleNonTerminal(int $ruleIndex): string
    {
        return $this->rules[$ruleIndex][0];
    }
}
