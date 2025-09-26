<?php

declare(strict_types = 1);

namespace Resrap\Examples\Math;

use Resrap\Component\Combinator\ScannerInterface;
use UnitEnum;

class FakeScanner implements ScannerInterface
{
    private int $pos = -1;

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
        $this->pos++;
        if ($this->pos >= count($this->strings)) {
            $this->pos--;
            return ScannerInterface::EOF;
        }
        return $this->tokens[$this->pos];
    }

    public function value(): ?string
    {
        return $this->strings[$this->pos] ?? null;
    }
}
