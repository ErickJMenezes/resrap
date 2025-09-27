<?php

declare(strict_types=1);

namespace Resrap\Component\Scanner;

enum ScannerToken
{
    /**
     * Instructs the scanner to skip the current token.
     */
    case SKIP;

    /**
     * Represents the end of the token stream.
     */
    case EOF;

    /**
     * Returned by scanner when some error occurs.
     */
    case ERROR;
}
