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
final class RegexScanner implements Scanner
{
    private ?string $value = null;

    private InputBuffer $buffer;

    private ?Position $lastTokenPosition = null;

    /**
     * @param array<string, Closure(string&,array): (int|UnitEnum)> $patterns
     */
    public function __construct(private readonly array $patterns)
    {
    }

    public function lex(): UnitEnum
    {
        if ($this->buffer->eof) {
            return ScannerToken::EOF;
        }
        do {
            $token = $this->tokenize();
        } while ($token === ScannerToken::SKIP);
        if ($token === ScannerToken::ERROR) {
            throw ScannerException::unexpectedCharacter(substr($this->buffer->content, 0, 1));
        }
        return $token;
    }

    private function tokenize(): int|UnitEnum
    {
        if ($this->buffer->eof) {
            return ScannerToken::EOF;
        }
        foreach ($this->patterns as $regexp => $handler) {
            $matches = [];
            preg_match($regexp, $this->buffer->content, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $this->lastTokenPosition = $this->buffer->position;
            $value = array_shift($matches);
            $size = strlen($value);
            $this->buffer->consume($size);
            $handlerResult = $handler($value, $matches);
            $this->value = $value;
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
        $this->buffer = new InputBuffer($input);
    }

    public function lastTokenPosition(): Position
    {
        return $this->lastTokenPosition ?? $this->buffer->position;
    }
}
