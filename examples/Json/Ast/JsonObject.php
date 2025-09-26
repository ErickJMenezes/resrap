<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

/**
 * @phpstan-type PairList list<JsonPair>
 */
final readonly class JsonObject implements Node
{
    /**
     * @param array<int, JsonPair> $pairs
     */
    public function __construct(public array $pairs) {}
}
