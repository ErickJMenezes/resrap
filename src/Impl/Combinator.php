<?php

declare(strict_types = 1);

namespace Resrap\Component\Impl;

use Closure;
use InvalidArgumentException;
use Resrap\Component\Spec\CombinatorInterface;
use Resrap\Component\Spec\ScannerInterface;
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
final class Combinator implements CombinatorInterface
{
    /**
     * @var array<array-key, array<int, Combinator|UnitEnum|string>>
     */
    private array $combinations = [];

    /**
     * @var array<array-key, Closure(array<array-key, string>): mixed>
     */
    private array $thenCallbacks = [];

    private function __construct(public readonly string $name) {}

    /**
     * Creates a new instance of the Combinator class with the given name.
     *
     * @param string $name The name to be assigned to the Combinator instance.
     *
     * @return Combinator A new instance of the Combinator class.
     */
    public static function is(string $name): Combinator
    {
        return new self($name);
    }

    /**
     * Combines a sequence of elements into a pending sequence.
     *
     * @param (Closure(): Combinator|UnitEnum|string)|Combinator|UnitEnum|string ...$sequence The sequence of elements
     *                                                                           to combine. At least one element must
     *                                                                           be provided.
     *
     * @return PendingSequence A new PendingSequence instance created with the provided sequence.
     * @throws InvalidArgumentException If the sequence is empty.
     */
    public function or(Closure|Combinator|UnitEnum|string ...$sequence): PendingSequence
    {
        if (count($sequence) === 0) {
            throw new InvalidArgumentException("The sequence must have at least one element.");
        }
        $sequence = array_map(
            fn($item) => match (true) {
                $item === ":$this->name:" => $this,
                $item instanceof Closure => fn(): Combinator|UnitEnum|string => $item(),
                default => $item,
            },
            $sequence,
        );
        return new PendingSequence(function (Closure $whenMatches) use (&$sequence): Combinator {
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
        foreach ($this->combinations as $sKey => $sequence) {
            $currentPosition = $iterator->index();
            $parsed = [];
            foreach ($sequence as $matcher) {
                $token = $iterator->token();
                if ($token === ScannerInterface::EOF) {
                    $iterator->goto($currentPosition);
                    break;
                }
                if ($matcher instanceof Closure) {
                    // matcher created lazily to avoid recursive calls
                    $matcher = $matcher();
                }
                if ($matcher instanceof Combinator) {
                    try {
                        $parsed[] = $matcher->apply($iterator);
                        continue;
                    } catch (RuntimeException $e) {
                        $iterator->goto($currentPosition);
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
        throw new RuntimeException(
            "Unexpected value \"{$iterator->value()}\" found when parsing {$this->name} at position {$iterator->index()}.",
        );
    }
}
