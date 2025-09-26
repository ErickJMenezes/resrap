<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

/**
 * @phpstan-type NodeList list<Node>
 */
final readonly class JsonArray implements Node
{
    /**
     * @param array<int, Node> $items
     */
    public function __construct(public array $items) {}
}
