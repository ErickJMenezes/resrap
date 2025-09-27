<?php

declare(strict_types = 1);

namespace Resrap\Component\Combinator;

use Resrap\Component\Scanner\ScannerToken;
use UnitEnum;

/**
 * Represents an iterator for scanning and managing tokens and their associated values.
 * Provides methods for lexical scanning, accessing current token values, and managing the position within the token
 * collection.
 */
final class ScannerIterator
{
    /** @var array<UnitEnum|int> */
    private array $tokens = [];

    /** @var array<string> */
    private array $values = [];

    private int $pos = -1;

    public function __construct(
        private readonly ScannerInterface $scanner,
    ) {
        $this->advance();
    }

    public function token(): int|UnitEnum
    {
        return $this->tokens[$this->pos] ?? ScannerToken::EOF;
    }

    public function value(): ?string
    {
        return $this->values[$this->pos] ??= $this->scanner->value();
    }

    public function goto(int $index): void
    {
        $this->pos = $index;
    }

    public function index(): int
    {
        return $this->pos;
    }

    public function advance(): void
    {
        $this->tokens[++$this->pos] ??= $this->scanner->lex();
    }
}
