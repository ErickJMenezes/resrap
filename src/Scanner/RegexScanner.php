<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

use UnitEnum;

/**
 * Represents a scanner that uses regular expressions to tokenize input strings.
 * Implements the ScannerInterface.
 *
 * This class processes a given input string and matches it against a series
 * of regular expression patterns provided during instantiation. It extracts
 * tokens based on these patterns and executes corresponding handler functions.
 */
final class RegexScanner implements ScannerInterface
{
    private ?string $value = null;

    private string $input;

    private bool $eof {
        get => strlen($this->input) === 0;
    }

    /**
     * @param array<string, Closure(string&,array): (int|UnitEnum)> $patterns
     */
    public function __construct(private readonly array $patterns)
    {
        $this->input = '';
    }

    public function lex(): UnitEnum
    {
        if ($this->eof) {
            return ScannerToken::EOF;
        }
        do {
            $token = $this->tokenize();
        } while ($token === ScannerToken::SKIP);
        if ($token === ScannerToken::ERROR) {
            throw ScannerException::unexpectedCharacter(substr($this->input, 0, 1));
        }
        return $token;
    }

    private function tokenize(): int|UnitEnum
    {
        if ($this->eof) {
            return ScannerToken::EOF;
        }
        foreach ($this->patterns as $regexp => $handler) {
            $matches = [];
            preg_match($regexp, $this->input, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $value = array_shift($matches);
            $size = strlen($value);
            $handlerResult = ($handler)($value, $matches);
            $this->value = $value;
            $this->input = substr($this->input, $size);
            return $handlerResult;
        }
        return ScannerToken::ERROR;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function setInput(string $input): void
    {
        $this->input = $input;
        $this->value = null;
    }
}
