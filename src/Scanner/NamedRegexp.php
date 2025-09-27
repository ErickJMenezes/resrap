<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

final readonly class NamedRegexp
{
    public function __construct(public string $name, public string $pattern) {}
}
