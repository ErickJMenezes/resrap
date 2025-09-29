<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

use Resrap\Component\Parser\Trie\GrammarTree;
use Resrap\Component\Parser\Trie\GrammarTreeBuilder;
use Resrap\Component\Scanner\ScannerInterface;
use Resrap\Component\Scanner\ScannerIterator;
use Resrap\Component\Scanner\ScannerIteratorInterface;
use UnitEnum;

final class Parser
{
    private readonly ScannerIteratorInterface $iterator;

    private readonly GrammarRule $grammar;

    private readonly GrammarTreeBuilder $grammarTreeBuilder;

    public function __construct(ScannerInterface $scanner, GrammarRule $grammar)
    {
        $this->iterator = new ScannerIterator($scanner);
        $this->grammar = $grammar;
        $this->grammarTreeBuilder = new GrammarTreeBuilder();
    }

    public function parse(): mixed
    {
        $tree = $this->grammarTreeBuilder->build($this->grammar);
        $result = $this->apply($tree);
        return $result;
    }

    private function buildTreeAndApply(GrammarRule $grammar): mixed
    {
        return $this->apply($this->grammarTreeBuilder->build($grammar));
    }

    private function apply(GrammarTree $trie)
    {
        return $this->iterateChildren($trie->children);
    }

    /**
     * @param array<GrammarTree> $children
     *
     * @return mixed|ParserToken
     */
    private function iterateChildren(array $children, array $carry = []): mixed
    {
        $startPosition = $this->iterator->key();
        $parsed = $carry;
        foreach ($children as $child) {
            $token = $this->iterator->current();
            $matcher = $child->matcher;

            if ($matcher instanceof GrammarRule) {
                $macherResult = $this->buildTreeAndApply($matcher);
                if ($macherResult === ParserToken::EXHAUSTED) {
                    $this->iterator->goto($startPosition);
                    continue;
                }
                $parsed[] = $macherResult;
            } elseif ($matcher instanceof UnitEnum) {
                if ($token !== $matcher) {
                    continue;
                }
                $parsed[] = $this->iterator->value();
                $this->iterator->next();
            } elseif (is_string($matcher)) {
                if (ord($matcher) !== $token) {
                    continue;
                }
                $parsed[] = $this->iterator->value();
                $this->iterator->next();
            }
            if (count($child->children) > 0) {
                $childResult = $this->iterateChildren($child->children, $parsed);
                if ($childResult !== ParserToken::EXHAUSTED) {
                    return $childResult;
                }
            }
            if ($child->isTerminal) {
                return ($child->callback)($parsed);
            }
        }
        return ParserToken::EXHAUSTED;
    }
}
