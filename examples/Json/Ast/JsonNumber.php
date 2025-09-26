<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

final readonly class JsonNumber implements Node
{
    public function __construct(public string $value) {}
}
