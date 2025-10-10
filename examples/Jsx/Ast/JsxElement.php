<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx\Ast;

final readonly class JsxElement implements Node
{
    /**
     * @param string $tagName
     * @param JsxAttribute[] $attributes
     * @param Node[] $children
     */
    public function __construct(
        public string $tagName,
        public array $attributes,
        public array $children
    ) {}
    
    public function toString(): string
    {
        $attrs = '';
        if (!empty($this->attributes)) {
            $attrs = ' ' . implode(' ', array_map(
                fn(JsxAttribute $attr) => $attr->toString(),
                $this->attributes
            ));
        }
        
        // Self-closing se não tem filhos
        if (empty($this->children)) {
            return "<{$this->tagName}{$attrs} />";
        }
        
        $childrenStr = implode('', array_map(
            fn(Node $child) => $child->toString(),
            $this->children
        ));
        
        return "<{$this->tagName}{$attrs}>{$childrenStr}</{$this->tagName}>";
    }
}