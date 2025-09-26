<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Ast;

final readonly class JsonBoolean implements Node
{
    public function __construct(public bool $value) {}
}
