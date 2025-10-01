<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser\Trie;

use Closure;
use UnitEnum;

final class GrammarTree
{
    /**
     * @var array<string, self>
     */
    public array $children = [];

    public bool $isTerminal = false;

    public ?int $sequenceKey = null;

    /**
     * @var null|Closure(array<array-key, string>): mixed
     */
    public ?Closure $callback = null;

    public null|UnitEnum|string $matcher = null;

    public bool $isEmptySequence = false;

    public TreeKind $kind = TreeKind::LEAF;

    public ?self $branch = null;

    /**
     * @var array<UnitEnum|string>
     */
    public array $lookahead = [];
}
