<?php

declare(strict_types = 1);

namespace Resrap\Examples\Math;

use Resrap\Component\Spec\ScannerInterface;
use UnitEnum;

class FakeScanner implements ScannerInterface
{
    private int $pos = 0;

    private array $strings;

    private array $tokens;

    public function __construct()
    {
        $this->strings = [
            '1',
            '+',
            '10',
            '*',
            '100',
            '-',
            '5',
        ];
        $this->tokens = [
            Token::NUMBER,
            Token::PLUS,
            Token::NUMBER,
            Token::MULTIPLY,
            Token::NUMBER,
            Token::MINUS,
            Token::NUMBER,
        ];
    }

    public function lex(): int|UnitEnum
    {
        return $this->tokens[$this->pos];
    }

    public function value(): ?string
    {
        return $this->strings[$this->pos];
    }

    public function advance(): void
    {
        $this->pos++;
    }

    public function goto(int $index): void
    {
        $this->pos = $index;
    }

    public function index(): int
    {
        return $this->pos;
    }

    public function eof(): bool
    {
        return $this->pos >= count($this->strings);
    }
}
