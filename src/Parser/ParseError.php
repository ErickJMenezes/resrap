<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

final readonly class ParseError
{
    public function __construct(
        public int $position,
        public string $found,
        public array $expected
    ) {}

    public function format(): string {
        $exp = implode(", ", $this->expected);
        return "Parse error at position {$this->position}: ".
            "expected {$exp}, found '{$this->found}'";
    }

    public static function furthest(self $first, self $second): self
    {
        return $first->position >= $second->position ? $first : $second;
    }
}
