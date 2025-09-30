<?php

declare(strict_types=1);

namespace Resrap\Component\Ebnf;

use Resrap\Component\Ebnf\Ast\GrammarFile;
use Resrap\Component\Parser\Parser;

/**
 * A parser specifically designed for handling EBNF (Extended Backus-Naur Form) grammar definitions.
 * This parser utilizes a specific scanner and grammar configuration to interpret and process
 * EBNF input strings, producing a structured grammar representation as output.
 */
final readonly class EbnfParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser(EbnfScanner::create(), EbnfGrammar::file());
    }

    public function parse(string $input): GrammarFile
    {
        return $this->parser->parse($input);
    }
}
