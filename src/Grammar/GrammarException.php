<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

use RuntimeException;

final class GrammarException extends RuntimeException
{
    /**
     * @param array<string> $directives
     *
     * @return self
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    public static function missingDirectives(array $directives): self
    {
        return new self('Missing directives in grammar file: '.implode(', ', $directives).'.');
    }
}
