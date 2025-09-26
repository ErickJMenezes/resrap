<?php

declare(strict_types = 1);

namespace Resrap\Component\Combinator;

use Resrap\Component\Spec\ScannerInterface;
use UnitEnum;

/**
 * Represents an iterator for scanning and managing tokens and their associated values.
 * Provides methods for lexical scanning, accessing current token values, and managing the position within the token
 * collection.
 *
 * @property-read list<TToken> $tokens A list of tokens that have been scanned and managed by the iterator.
 * @property-read list<string> $values A list of string values associated with the tokens.
 * @property-read int          $pos    The current position in the collection of tokens and values.
 *
 * @template TToken of (UnitEnum|int)
 */
final class ScannerIterator
{
    /** @var list<TToken> */
    private array $tokens = [];

    /** @var list<string> */
    private array $values = [];

    private int $pos = -1;

    public function __construct(
        private readonly ScannerInterface $scanner,
    ) {
        $this->advance();
    }

    public function token(): int|UnitEnum
    {
        return $this->tokens[$this->pos] ?? ScannerInterface::EOF;
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
