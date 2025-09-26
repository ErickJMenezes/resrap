<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

final readonly class JsonPair implements Node
{
    public function __construct(public string $key, public Node $value) {}
}
