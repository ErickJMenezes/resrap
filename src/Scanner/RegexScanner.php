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
 *
 * @internal This class is not part of the public API and may be subject to change or removal in future releases.
 */
final class RegexScanner implements Scanner
{
    private ?string $value = null;

    private InputBuffer $buffer;

    private ?Position $lastTokenPosition = null;

    /**
     * @param State[] $states
     */
    public function __construct(private State $activeState, private array $states) {}

    public function lex(): UnitEnum
    {
        if ($this->buffer->eof) {
            return ScannerToken::EOF;
        }
        [$token, $consumed] = $this->scanNextToken();
        $nextStateName = $this->activeState->getTransitionStateFor($token);
        if ($nextStateName !== false) {
            $this->activeState = $this->states[$nextStateName];
        }
        if ($consumed > 0) {
            $this->buffer->consume($consumed);
        }
        return $token;
    }

    /**
     * @return array{int|UnitEnum, int}
     */
    private function scanNextToken(): array
    {
        do {
            [$token, $consumed] = $this->applyPatterns();
            if ($token === ScannerToken::SKIP) {
                $this->buffer->consume($consumed);
                continue;
            }
            break;
        } while (true);
        if ($token === ScannerToken::ERROR) {
            throw ScannerException::unexpectedCharacter(
                substr($this->buffer->content, 0, 1),
                $this->lastTokenPosition,
            );
        }
        return [$token, $consumed];
    }

    /**
     * @return array{int|UnitEnum, int}
     */
    private function applyPatterns(): array
    {
        if ($this->buffer->eof) {
            return [ScannerToken::EOF, 0];
        }
        foreach ($this->activeState->patterns as $regexp => $handler) {
            if ($handler instanceof ManualPattern) {
                $result = $handler->scan($this->buffer->content);

                if ($result !== null) {
                    [$token, $size, $value] = $result;
                    $this->lastTokenPosition = $this->buffer->position;
                    $this->value = $value;
                    return [$token, $size];
                }

                continue;
            }
            $matches = [];
            preg_match($regexp, $this->buffer->content, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $this->lastTokenPosition = $this->buffer->position;
            $value = array_shift($matches);
            $size = strlen($value);
            $handlerResult = $handler($value, $matches);
            $this->value = $value;
            return [$handlerResult, $size];
        }
        return [ScannerToken::ERROR, 0];
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function setInput(InputBuffer $input): void
    {
        $this->buffer = $input;
    }

    public function lastTokenPosition(): Position
    {
        return $this->lastTokenPosition ?? $this->buffer->position;
    }
}
