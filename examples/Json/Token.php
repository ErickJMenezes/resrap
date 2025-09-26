<?php

declare(strict_types=1);

namespace Resrap\Examples\Json;

enum Token
{
    case LBRACE;      // {
    case RBRACE;      // }
    case LBRACKET;    // [
    case RBRACKET;    // ]
    case COLON;       // :
    case COMMA;       // ,
    case STRING;      // "..."
    case NUMBER;      // 0, -1, 1.23, 1e10, etc
    case TRUE;        // true
    case FALSE;       // false
    case NULL;        // null
}
