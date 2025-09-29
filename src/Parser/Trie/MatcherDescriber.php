<?php

declare(strict_types = 1);

namespace Resrap\Component\Parser\Trie;

use Resrap\Component\Parser\GrammarRule;
use Stringable;
use UnitEnum;

/**
 * Represents a describer for matcher objects capable of converting them into string representations.
 * This class implements the Stringable interface and provides various string formats based on the type of the matcher.
 *
 * - If the matcher is an instance of GrammarRule, the string representation follows the format 'grammar#<name>'.
 * - If the matcher is an instance of UnitEnum, the string representation follows the format 'enum#<name>'.
 * - If the matcher is a string, it is prefixed with 'str#' in the resulting string.
 *
 * The matcher can be of types GrammarRule, UnitEnum, or string.
 */
final readonly class MatcherDescriber implements Stringable
{
    public function __construct(private GrammarRule|UnitEnum|string $matcher) {}

    public function __toString(): string
    {
        if ($this->matcher instanceof GrammarRule) {
            return 'rule#'.$this->matcher->name;
        }
        if ($this->matcher instanceof UnitEnum) {
            return 'token#'.$this->matcher->name;
        }
        return 'string#'.$this->matcher;
    }
}
