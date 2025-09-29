<?php

declare(strict_types=1);

namespace Resrap\Component\Parser;

final readonly class ParseResult
{
    private function __construct(
        public bool $ok,
        public mixed $value,
        public ?ParseError $expected,
    ) {}

    public static function success(mixed $value): self {
        return new self(true, $value, null);
    }

    public static function failure(ParseError $error): self {
        return new self(false, null, $error);
    }
}
