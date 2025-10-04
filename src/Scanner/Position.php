<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

final readonly class Position
{
    public function __construct(
        public int $offset,
        public int $line,
        public int $column,
    ) {}
}
