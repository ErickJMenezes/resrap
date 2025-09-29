<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

use Resrap\Component\Parser\Trie\GrammarTree;
use Resrap\Component\Parser\Trie\GrammarTreeBuilder;
use Resrap\Component\Scanner\ScannerInterface;
use Resrap\Component\Scanner\ScannerIterator;
use Resrap\Component\Scanner\ScannerIteratorInterface;
use Resrap\Component\Scanner\ScannerToken;
use UnitEnum;

/**
 * The Parser class is responsible for parsing input data based on a given grammar.
 * It uses a scanner to iterate through input tokens and a grammar tree builder
 * to match and process the grammar rules.
 * The class handles the parsing process
 * recursively, applying rules and generating structured output or errors as needed.
 */
final readonly class Parser
{
    private ScannerIteratorInterface $iterator;

    private GrammarRule $grammar;

    private GrammarTreeBuilder $grammarTreeBuilder;

    public function __construct(ScannerInterface $scanner, GrammarRule $grammar)
    {
        $this->iterator = new ScannerIterator($scanner);
        $this->grammar = $grammar;
        $this->grammarTreeBuilder = new GrammarTreeBuilder();
    }

    public function parse(): mixed
    {
        $result = $this->buildTreeAndApply($this->grammar);
        if (!$result->ok) {
            throw new ParserException($result->error->format());
        }
        $current = $this->iterator->current();
        if ($current !== ScannerToken::EOF) {
            $value = $current instanceof UnitEnum ? $current->name : $this->iterator->value();
            throw new ParserException("Unexpected token: {$value} at position {$this->iterator->key()}. Expected EOF.");
        }
        return $result->value;
    }

    private function buildTreeAndApply(GrammarRule $grammar): ParseResult
    {
        return $this->apply($this->grammarTreeBuilder->build($grammar));
    }

    private function apply(GrammarTree $trie): ParseResult
    {
        return $this->iterateChildren($trie->children);
    }

    /**
     * @param array<GrammarTree> $children
     * @param array              $carry
     *
     * @return ParseResult
     */
    private function iterateChildren(array $children, array $carry = []): ParseResult
    {
        $furthestError = null;
        $startPosition = $this->iterator->key();
        $parsed = $carry;
        foreach ($children as $child) {
            $token = $this->iterator->current();
            $matcher = $child->matcher;

            if ($matcher instanceof GrammarRule) {
                $macherResult = $this->buildTreeAndApply($matcher);
                if (!$macherResult->ok) {
                    $furthestError = ParseError::furthestBetween($furthestError, $macherResult->error);
                    $this->iterator->goto($startPosition);
                    continue;
                }
                $parsed[] = $macherResult->value;
            } elseif ($matcher instanceof UnitEnum) {
                if ($token !== $matcher) {
                    $furthestError = ParseError::furthestBetween($furthestError, new ParseError(
                        $this->iterator->key(),
                        $token instanceof UnitEnum ? $token->name : $this->iterator->value(),
                        [$matcher->name]
                    ));
                    continue;
                }
                $parsed[] = $this->iterator->value();
                $this->iterator->next();
            } elseif (is_string($matcher)) {
                if (ord($matcher) !== $token) {
                    $furthestError = ParseError::furthestBetween($furthestError, new ParseError(
                        $this->iterator->key(),
                        $this->iterator->value(),
                        [$matcher]
                    ));
                    continue;
                }
                $parsed[] = $this->iterator->value();
                $this->iterator->next();
            }
            if (count($child->children) > 0) {
                $childResult = $this->iterateChildren($child->children, $parsed);
                if ($childResult->ok) {
                    return $childResult;
                }
                $furthestError = ParseError::furthestBetween($furthestError, $childResult->error);
            }
            if ($child->isTerminal) {
                return ParseResult::success(($child->callback)($parsed));
            }
        }
        return ParseResult::failure($furthestError ?? new ParseError(
            $this->iterator->key(),
            $this->iterator->value(),
            ['<unknown>'],
        ));
    }
}
