<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Ast;

final readonly class UseStatement implements Node
{
    /**
     * @param string   $name
     * @param string[] $values
     */
    public function __construct(
        public string $name,
    ) {}
}
