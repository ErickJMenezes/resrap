<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar;

enum Token
{
    case IDENTIFIER;            // expr, number, etc.
    case ASSIGN;                // :=
    case PIPE;                  // |
    case SEMICOLON;             // ;
    case STRING;                // "foo"
    case CHAR;                  // 'a'
    case COMMENT;               // (skip)
    case CODE_BLOCK;            // { ... }
    // Special instructions
    case DEFINE_CLASSNAME;      // %class FooBar;
    case BACKSLASH;             // \
    case USE;                   // %use FooBar;
    case AS;                   // %use FooBar as F;
    case START;                 // %start foo;
    case STATIC_ACCESS;         // ::
}
