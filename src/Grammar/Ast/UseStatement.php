<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Ast;

final readonly class UseStatement extends Directive
{
    public function __construct(
        string $value,
        public ?string $alias = null,
    ) {
        parent::__construct('use', $value);
    }
}
