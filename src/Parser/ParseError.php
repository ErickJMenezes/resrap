<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser;

final readonly class ParseError
{
    /**
     * @param int           $position
     * @param string        $found
     * @param array<string> $expected
     */
    public function __construct(
        public int $position,
        public string $found,
        public array $expected,
    ) {}

    public static function furthestBetween(?self $first, self $second): self
    {
        if ($first === null) {
            return $second;
        }
        if ($first->position === $second->position) {
            $expected = array_merge($first->expected, $second->expected);
            return new self($first->position, $first->found, $expected);
        }
        return $first->position > $second->position ? $first : $second;
    }

    public function format(): string
    {
        $exp = implode(", ", $this->expected);
        return "Parse error at position {$this->position}: ".
            "expected {$exp}, found '{$this->found}'";
    }
}
