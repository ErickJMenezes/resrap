<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class TextNode implements Node
{
    public function __construct(
        public string $text
    ) {}
    
    public function toString(): string
    {
        return $this->text;
    }
}