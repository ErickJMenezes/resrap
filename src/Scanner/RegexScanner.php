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

    /**
     * @param array<string, Closure(string&): (int|UnitEnum)> $patterns
     */
    public function __construct(private readonly array $patterns)
    {
        $this->input = '';
    }

    public function lex(): UnitEnum
    {
        if (strlen($this->input) === 0) {
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
        foreach ($this->patterns as $regexp => $handler) {
            $matches = [];
            preg_match($regexp, $this->input, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);
            if (empty($matches[0])) {
                continue;
            }
            $value = $matches[0][0];
            $handlerResult = ($handler)($value);
            $this->value = $value;
            $this->input = substr($this->input, strlen($matches[0][0]));
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
