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
        return new Parser('json_value')
            ->is(self::object(...))
            ->then(fn(array $m) => $m[0])
            //
            ->is(self::array(...))
            ->then(fn(array $m) => $m[0])
            //
            ->is(Token::STRING)
            ->then(fn(array $m) => new JsonString($m[0]))
            //
            ->is(Token::NUMBER)
            ->then(fn(array $m) => new JsonNumber($m[0]))
            //
            ->is(Token::TRUE)
            ->then(fn(array $m) => new JsonBoolean(true))
            //
            ->is(Token::FALSE)
            ->then(fn(array $m) => new JsonBoolean(false))
            //
            ->is(Token::NULL)
            ->then(fn(array $m) => new JsonNull());
    }

    public static function object(): Parser
    {
        return new Parser('json_object')
            ->is(Token::LBRACE, Token::RBRACE) // { }
            ->then(fn(array $m) => new JsonObject([]))
            // { members }
            ->is(Token::LBRACE, self::members(), Token::RBRACE)
            ->then(fn(array $m) => new JsonObject($m[1]));
    }

    public static function members(): Parser
    {
        return new Parser('json_object_members')
            ->is(self::pair(...))
            ->then(fn(array $m) => [$m[0]])
            // pair, members
            ->is(self::pair(...), Token::COMMA, self::members(...))
            ->then(fn(array $m) => array_merge([$m[0]], $m[2]));
    }

    public static function pair(): Parser
    {
        return new Parser('json_object_pair')
            ->is(Token::STRING, Token::COLON, self::value())
            ->then(fn(array $m) => new JsonPair($m[0], $m[2]));
    }

    public static function array(): Parser
    {
        return new Parser('json_array')
            ->is(Token::LBRACKET, Token::RBRACKET) // []
            ->then(fn(array $m) => new JsonArray([]))
            // [ elements ]
            ->is(Token::LBRACKET, self::elements(...), Token::RBRACKET)
            ->then(fn(array $m) => new JsonArray($m[1]));
    }

    public static function elements(): Parser
    {
        return new Parser('json_array_elements')
            ->is(self::value(...))
            ->then(fn(array $m) => [$m[0]])
            // value , elements
            ->is(self::value(...), Token::COMMA, self::elements(...))
            ->then(fn(array $m) => array_merge([$m[0]], $m[2]));
    }
}
