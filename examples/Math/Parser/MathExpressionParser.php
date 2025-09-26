<?php

declare(strict_types = 1);

namespace Resrap\Examples\Math\Parser;

use Resrap\Component\Impl\Combinator;
use Resrap\Examples\Math\Ast\MathExpression;
use Resrap\Examples\Math\Ast\MathOperator;
use Resrap\Examples\Math\Ast\Number;
use Resrap\Examples\Math\Token;

final class MathExpressionParser
{
    public static function expression(): Combinator
    {
        return Combinator::is('math_expression')
            ->or(self::number())
            ->then(fn(array $m) => $m[0])
            //
            ->or(self::number(), self::operator(), ':math_expression:')
            ->then(fn(array $m) => new MathExpression([$m[0], $m[1], $m[2]]));
    }

    public static function number(): Combinator
    {
        return Combinator::is('math_number')
            ->or(Token::NUMBER)
            ->then(fn(array $m) => new Number($m[0]));
    }

    public static function operator(): Combinator
    {
        $whenMatches = fn(array $m) => new MathOperator($m[0]);
        return Combinator::is('math_operator')
            ->or(Token::PLUS)
            ->then($whenMatches)
            //
            ->or(Token::MINUS)
            ->then($whenMatches)
            //
            ->or(Token::MULTIPLY)
            ->then($whenMatches)
            //
            ->or(Token::DIVIDE)
            ->then($whenMatches);
    }
}
