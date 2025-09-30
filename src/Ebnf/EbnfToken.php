<?php

declare(strict_types = 1);

namespace Resrap\Component\Ebnf;

enum EbnfToken
{
    case IDENTIFIER;            // expr, number, etc.
    case ASSIGN;                // :=
    case PIPE;                  // |
    case SEMICOLON;             // ;
    case STRING;                // "foo"
    case CHAR;                  // 'a'
    case COMMENT;               // (skip)
    case CODE_BLOCK;            // { ... }
    // Special php instructions
    case CLASSNAME;             // %class FooBar
    case QUALIFIED_IDENTIFIER;  // Foo | \Foo | Foo\Bar
    case USE;                   // %use FooBar
    case STATIC_ACCESS;         // ::
}
