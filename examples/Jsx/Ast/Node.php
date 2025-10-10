<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

interface Node
{
    /**
     * Converte o nó AST de volta para código
     */
    public function toString(): string;
}