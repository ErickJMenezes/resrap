<?php

declare(strict_types=1);

namespace Resrap\Examples\Json\Parser;

use Resrap\Component\Combinator\Parser;
use Resrap\Examples\Json\Ast\JsonArray;
use Resrap\Examples\Json\Ast\JsonBoolean;
use Resrap\Examples\Json\Ast\JsonNull;
use Resrap\Examples\Json\Ast\JsonNumber;
use Resrap\Examples\Json\Ast\JsonObject;
use Resrap\Examples\Json\Ast\JsonPair;
use Resrap\Examples\Json\Ast\JsonString;
use Resrap\Examples\Json\Token;

final class JsonParser
{
    public static function value(): Parser
    {
        return Parser::is(self::object(...))
            ->then(fn(array $m) => $m[0])
            //
            ->or(self::array(...))
            ->then(fn(array $m) => $m[0])
            //
            ->or(Token::STRING)
            ->then(fn(array $m) => new JsonString($m[0]))
            //
            ->or(Token::NUMBER)
            ->then(fn(array $m) => new JsonNumber($m[0]))
            //
            ->or(Token::TRUE)
            ->then(fn(array $m) => new JsonBoolean(true))
            //
            ->or(Token::FALSE)
            ->then(fn(array $m) => new JsonBoolean(false))
            //
            ->or(Token::NULL)
            ->then(fn(array $m) => new JsonNull());
    }

    public static function object(): Parser
    {
        return Parser::is(Token::LBRACE, Token::RBRACE) // { }
            ->then(fn(array $m) => new JsonObject([]))
            // { members }
            ->or(Token::LBRACE, self::members(), Token::RBRACE)
            ->then(fn(array $m) => new JsonObject($m[1]));
    }

    public static function members(): Parser
    {
        return Parser::is(self::pair(...))
            ->then(fn(array $m) => [$m[0]])
            // pair, members
            ->or(self::pair(...), Token::COMMA, self::members(...))
            ->then(fn(array $m) => array_merge([$m[0]], $m[2]));
    }

    public static function pair(): Parser
    {
        return Parser::is(Token::STRING, Token::COLON, self::value())
            ->then(fn(array $m) => new JsonPair($m[0], $m[2]));
    }

    public static function array(): Parser
    {
        return Parser::is(Token::LBRACKET, Token::RBRACKET) // []
            ->then(fn(array $m) => new JsonArray([]))
            // [ elements ]
            ->or(Token::LBRACKET, self::elements(...), Token::RBRACKET)
            ->then(fn(array $m) => new JsonArray($m[1]));
    }

    public static function elements(): Parser
    {
        return Parser::is(self::value(...))
            ->then(fn(array $m) => [$m[0]])
            // value , elements
            ->or(self::value(...), Token::COMMA, self::elements(...))
            ->then(fn(array $m) => array_merge([$m[0]], $m[2]));
    }
}
