<?php

declare(strict_types=1);

namespace Resrap\Component\Grammar\Ast;

readonly class Directive implements Node
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}
}
