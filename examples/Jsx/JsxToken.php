<?php

declare(strict_types = 1);

namespace Resrap\Examples\Jsx;

enum JsxToken
{
    // JavaScript tokens
    case CONST;
    case LET;
    case VAR;
    case FUNCTION;
    case IDENTIFIER;
    case NUMBER;
    case STRING;
    case EQUALS;
    case SEMICOLON;
    case COMMA;
    
    // JSX tokens
    case JSX_TAG_OPEN;        // <Component
    case JSX_TAG_CLOSE;       // >
    case JSX_TAG_SELF_CLOSE;  // />
    case JSX_TAG_END_OPEN;    // </
    case JSX_ATTR_NAME;
    case JSX_TEXT;
    
    // Delimiters
    case BRACE_OPEN;          // {
    case BRACE_CLOSE;         // }
    case PAREN_OPEN;          // (
    case PAREN_CLOSE;         // )
    
    // Operators
    case PLUS;
    case MINUS;
    case STAR;
    case SLASH;
}