<?php

declare(strict_types=1);

namespace Resrap\Component\Parser\Trie;

enum TreeKind
{
    case ROOT;
    case BRANCH;
    case LEAF;
}
