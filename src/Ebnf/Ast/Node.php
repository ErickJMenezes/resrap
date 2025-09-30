<?php

declare(strict_types=1);

namespace Resrap\Component\Ebnf\Ast;

interface Node
{
    public function compile(): string;
}
