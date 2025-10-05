<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

use Resrap\Component\Scanner\Scanner;
use Resrap\Component\Scanner\ScannerToken;
use RuntimeException;

/**
 * LALR Parser
 */
final readonly class Parser
{
    public function __construct(
        private array $actions,
        private array $goto,
        private array $callbacks,
        private array $rules,
        private Scanner $scanner,
    ) {}

    public static function fromGrammar(GrammarRule $root, Scanner $scanner)
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
                if ($token === ScannerToken::EOF) {
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
                    if ($ruleLength > 0) {
                        $args = array_splice($valueStack, -$ruleLength);
                        array_splice($stateStack, -$ruleLength);
                    } else {
                        $args = [];
                        // Does not remove when the rule is empty
                    }

                    // execute callback
                    $callbackResult = $callback($args);
                    $valueStack[] = $callbackResult;

                    // check goto
                    $currentState = end($stateStack);
                    $ruleNonTerminal = $this->getRuleNonTerminal($ruleIndex);
                    $nextState = $this->goto[$currentState][$ruleNonTerminal] ?? null;

                    if ($nextState === null) {
                        // Special case: If no GOTO and we are reducing the initial rule,
                        // it means we finished parsing successfully
                        if (count($stateStack) === 1 && $currentState === 0) {
                            // Estamos no estado inicial, parsing completo
                            return $callbackResult;
                        }

                        throw new RuntimeException("GOTO[$currentState][$ruleNonTerminal] not found.");
                    }

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
