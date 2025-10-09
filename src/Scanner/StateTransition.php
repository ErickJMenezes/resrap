<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use UnitEnum;

final readonly class StateTransition
{
    /**
     * @param UnitEnum[] $tokens
     * @param string     $transitionStateName
     */
    public function __construct(
        public array $tokens,
        public string $transitionStateName,
    ) {}
}
