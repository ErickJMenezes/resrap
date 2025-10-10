<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use UnitEnum;

final readonly class State
{
    /**
     * @param string                                                                        $name
     * @param array<string, (ManualPattern|Closure(string&,array,Scanner): (int|UnitEnum))> $patterns
     * @param StateTransition[]                                                             $transitions
     */
    public function __construct(
        public string $name,
        public array $patterns,
        private array $transitions = [],
    ) {}

    public function getTransitionStateFor(UnitEnum $token): false|string
    {
        foreach ($this->transitions as $transition) {
            if (in_array($token, $transition->tokens)) {
                return $transition->transitionStateName;
            }
        }
        return false;
    }
}
