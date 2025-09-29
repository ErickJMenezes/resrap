<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use InvalidArgumentException;
use Iterator;
use UnitEnum;

/**
 * ScannerIterator provides an implementation of the ScannerIteratorInterface.
 *
 * This class maintains the current position in the token stream, processes tokens
 * via the scanner, and offers methods to retrieve both tokens and their associated values.
 *
 * @template-implements Iterator<int, UnitEnum|int>
 */
final class ScannerIterator implements ScannerIteratorInterface
{
    /** @var array<UnitEnum|int> */
    private array $tokens = [];

    /** @var array<string> */
    private array $values = [];

    private int $pos = -1;

    private int $farthestAdvance = -1;

    public function __construct(
        private readonly ScannerInterface $scanner,
    ) {
        $this->advance();
    }

    public function next(): void
    {
        if ($this->current() === ScannerToken::EOF) {
            return;
        }
        $this->advance();
    }

    public function current(): int|UnitEnum
    {
        return $this->tokens[$this->pos] ?? ScannerToken::EOF;
    }

    private function advance(): void
    {
        if ($this->isCurrentlyAtFarthestPosition()) {
            $this->farthestAdvance++;
        }
        $this->tokens[++$this->pos] ??= $this->scanner->lex();
        if ($this->current() === ScannerToken::EOF) {
            return;
        }
        $this->values[$this->pos] ??= $this->scanner->value();
    }

    private function isCurrentlyAtFarthestPosition(): bool
    {
        return $this->pos === $this->farthestAdvance;
    }

    public function value(): ?string
    {
        return $this->values[$this->pos] ?? null;
    }

    public function goto(int $index): void
    {
        if ($index > $this->farthestAdvance || $index < 0) {
            throw new InvalidArgumentException(
                "Cannot goto to a index that wasn't previously advanced or is negative.",
            );
        }
        $this->pos = $index;
    }

    public function key(): int
    {
        return $this->pos;
    }

    public function valid(): bool
    {
        return $this->current() !== ScannerToken::EOF;
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    public function farthest(): array
    {
        return [$this->tokens[$this->farthestAdvance], $this->values[$this->farthestAdvance], $this->farthestAdvance];
    }
}
