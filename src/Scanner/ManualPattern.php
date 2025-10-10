<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use Closure;
use UnitEnum;

final readonly class ManualPattern
{
    /**
     * @param (Closure(string): array{UnitEnum, int, string}) $scanner
     */
    public function __construct(
        private Closure $scanner,
    ) {}

    /**
     * @return array{UnitEnum, int, string}|null [token, bytesConsumed, value] | null
     */
    public function scan(string $buffer): ?array
    {
        return ($this->scanner)($buffer);
    }
}
