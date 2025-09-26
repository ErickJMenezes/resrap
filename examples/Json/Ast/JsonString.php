<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

final readonly class JsonString implements Node
{
    public function __construct(public string $value) {}
}
