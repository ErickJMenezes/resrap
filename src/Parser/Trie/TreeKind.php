<?php

declare(strict_types=1);

namespace Resrap\Component\Parser\Trie;

enum TreeKind
{
    case LEAF;
    case BRANCH;
}
