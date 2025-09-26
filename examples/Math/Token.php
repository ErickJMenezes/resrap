<?php

declare(strict_types=1);

namespace Resrap\Examples\Math;

enum Token
{
    case NUMBER;
    case PLUS;
    case MINUS;
    case DIVIDE;
    case MULTIPLY;
    case OPEN_PAREN;
    case CLOSE_PAREN;
}
