<?php

declare(strict_types = 1);

namespace Resrap\Component\Combinator;

use Closure;
use InvalidArgumentException;
use Resrap\Component\Scanner\ScannerToken;
use RuntimeException;
use UnitEnum;

/**
 * Represents a combinator that facilitates the creation and matching
 * of sequences comprising various combinators, enums, or strings.
 *
 * This class is immutable, and instances are created using the `is` static method.
 * A sequence of combinators or matchers can be added through the `or` method,
 * while empty sequences can be specified via the `empty` method.
 * The `apply` method processes input using registered sequences and invokes
 * associated callbacks upon successful matches.
 */
final class Parser
{
    /**
     * @var array<array-key, array<int, Parser|UnitEnum|string>>
     */
    private array $combinations = [];

    /**
     * @var array<array-key, Closure(array<array-key, string>): mixed>
     */
    private array $thenCallbacks = [];

    private function __construct() {}

    /**
     * Creates a pending sequence by evaluating a given sequence of elements.
     *
     * @param (Closure(): Parser|UnitEnum|string)|Parser|UnitEnum|string ...$sequence         The sequence of elements
     *                                                                                        to evaluate.
     *
     * @return PendingSequence A new PendingSequence instance based on the provided sequence.
     */
    public static function is(Closure|Parser|UnitEnum|string ...$sequence): PendingSequence
    {
        return new self()
            ->or(...$sequence);
    }

    /**
     * Combines a sequence of elements into a pending sequence.
     *
     * @param (Closure(): Parser|UnitEnum|string)|Parser|UnitEnum|string ...$sequence The sequence of elements
     *                                                                           to combine. At least one element must
     *                                                                           be provided.
     *
     * @return PendingSequence A new PendingSequence instance created with the provided sequence.
     * @throws InvalidArgumentException If the sequence is empty.
     */
    public function or(Closure|Parser|UnitEnum|string ...$sequence): PendingSequence
    {
        if (count($sequence) === 0) {
            throw new InvalidArgumentException("The sequence must have at least one element.");
        }
        $sequence = array_map(
            fn($item) => match (true) {
                $item instanceof Closure => fn(): Parser|UnitEnum|string => $item(),
                default => $item,
            },
            $sequence,
        );
        return new PendingSequence(function (Closure $whenMatches) use (&$sequence): Parser {
            $this->combinations = [$sequence, ...$this->combinations];
            $this->thenCallbacks = [$whenMatches, ...$this->thenCallbacks];
            return $this;
        });
    }

    /**
     * Applies a sequence of combinators to the provided scanner and executes the corresponding callback if a match is
     * found.
     *
     * @param ScannerIterator $iterator The scanner iterator that provides methods for navigating and matching tokens.
     *
     * @return mixed The result from the callback associated with the matched sequence.
     * @throws RuntimeException If no matching sequence is found or an unexpected value is encountered.
     */
    public function apply(ScannerIterator $iterator): mixed
    {
        $lastException = null;
        foreach ($this->combinations as $sKey => $sequence) {
            $currentPosition = $iterator->index();
            $parsed = [];
            foreach ($sequence as $matcher) {
                $token = $iterator->token();
                if ($token === ScannerToken::EOF) {
                    $iterator->goto($currentPosition);
                    break;
                }
                if ($matcher instanceof Closure) {
                    // matcher created lazily to avoid recursive calls
                    $matcher = $matcher();
                }
                if ($matcher instanceof Parser) {
                    try {
                        $parsed[] = $matcher->apply($iterator);
                        continue;
                    } catch (ParserException $e) {
                        $iterator->goto($currentPosition);
                        $lastException = $e;
                        break;
                    }
                }

                if ($matcher instanceof UnitEnum) {
                    if ($token === $matcher) {
                        $parsed[] = $iterator->value();
                        $iterator->advance();
                        continue;
                    }
                    break;
                }

                if (is_string($matcher) && ord($matcher) === $token) {
                    $parsed[] = $iterator->value();
                    $iterator->advance();
                    continue;
                }
                break;
            }
            if (count($parsed) === count($sequence)) {
                return $this->thenCallbacks[$sKey]($parsed);
            }
            $iterator->goto($currentPosition);
        }
        throw ParserException::noSuitableMatcherFound($lastException);
    }
}
