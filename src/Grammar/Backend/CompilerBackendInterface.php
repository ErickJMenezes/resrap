<?php

declare(strict_types = 1);

namespace Resrap\Component\Grammar\Backend;

use Resrap\Component\Grammar\Ast\GrammarFile;

/**
 * Interface for a compiler backend.
 *
 * @template T
 */
interface CompilerBackendInterface
{
    /**
     * Compiles the given abstract syntax tree (AST) into an appropriate representation.
     *
     * @param GrammarFile $ast The abstract syntax tree to be compiled.
     *
     * @return T
     * @throws CompileException
     */
    public function compile(GrammarFile $ast);
}
