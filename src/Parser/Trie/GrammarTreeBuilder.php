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

    public function treeFor()
    {

    }

    public function build(GrammarRule $grammar): GrammarTree
    {
        return $this->buildSubtree($grammar);
    }

    private function buildSubtree(GrammarRule $grammar): GrammarTree
    {
        if (isset($this->cache[$grammar->name])) {
            return $this->cache[$grammar->name];
        }
        $branch = new GrammarTree();
        $branch->kind = TreeKind::ROOT;
        $this->cache[$grammar->name] = $branch;
        $this->createGrammarTree($grammar, $branch);
        return $branch;
    }

    private function normalizeMatcher(GrammarRule|UnitEnum|string $matcher): string
    {
        return (string) new MatcherDescriber($matcher);
    }

    private function createGrammarTree(GrammarRule $grammar, GrammarTree $branch): void
    {
        foreach ($grammar->combinations as $seqKey => $sequence) {
            $node = $branch;
            foreach ($sequence as $mPos => $matcher) {
                if ($matcher instanceof Closure) {
                    $matcher = $matcher();
                }
                $key = $this->normalizeMatcher($matcher);
                $child = $node->children[$key] ??= new GrammarTree();
                if ($matcher instanceof GrammarRule) {
                    $child->kind = TreeKind::BRANCH;
                    $subtree = $this->buildSubtree($matcher);
                    $child->branch = $subtree;
                    $branch->lookahead = array_unique([
                        ...$branch->lookahead,
                        ...$subtree->lookahead,
                    ], SORT_REGULAR);
                } else {
                    $child->matcher = $matcher;
                    $child->kind = TreeKind::LEAF;
                    $branch->lookahead = array_unique([
                        ...$branch->lookahead,
                        $matcher,
                    ], SORT_REGULAR);
                }
                $node = $child;
            }
            $node->isTerminal = true;
            $node->sequenceKey = $seqKey;
            $node->callback = $grammar->callbacks[$seqKey];
            $node->isEmptySequence = count($sequence) === 0;
        }
    }
}
