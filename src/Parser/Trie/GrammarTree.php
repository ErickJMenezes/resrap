<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser\Trie;

use Closure;
use Resrap\Component\Parser\GrammarRule;
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

    public GrammarRule|UnitEnum|string $matcher;
}
