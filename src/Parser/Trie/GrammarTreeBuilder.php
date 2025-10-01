<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser\Trie;

use Closure;
use Resrap\Component\Parser\GrammarRule;
use UnitEnum;

/**
 * GrammarTreeBuilder is responsible for constructing a GrammarTree based on provided grammar rules and their associated combinations and callbacks.
 * It utilizes memoization to cache previously constructed grammar trees to optimize repeated builds.
 */
final class GrammarTreeBuilder
{
    /** @var array<GrammarTree> */
    private array $cache = [];

    public function build(GrammarRule $grammar): GrammarTree
    {
        return $this->buildSubtree($grammar);
    }

    private function buildSubtree(GrammarRule $grammar): GrammarTree
    {
        if (isset($this->cache[$grammar->name])) {
            return $this->cache[$grammar->name];
        }
        $root = $this->cache[$grammar->name] = new GrammarTree();
        $root->matcher = $grammar;
        $this->createGrammarTree($grammar, $root);
        return $root;
    }

    private function normalizeMatcher(GrammarRule|UnitEnum|string $matcher): string
    {
        return (string) new MatcherDescriber($matcher);
    }

    private function createGrammarTree(GrammarRule $grammar, GrammarTree $root): void
    {
        foreach ($grammar->combinations as $seqKey => $sequence) {
            $node = $root;
            foreach ($sequence as $matcher) {
                if ($matcher instanceof Closure) {
                    $matcher = $matcher();
                }
                $key = $this->normalizeMatcher($matcher);
                if (!isset($node->children[$key])) {
                    $node->children[$key] = new GrammarTree();
                    $node->children[$key]->matcher = $matcher;
                }
                $node = $node->children[$key];
            }
            $node->isTerminal = true;
            $node->sequenceKey = $seqKey;
            $node->callback = $grammar->callbacks[$seqKey];
            $node->isEmptySequence = count($sequence) === 0;
        }
    }
}
