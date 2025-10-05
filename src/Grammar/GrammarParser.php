<?php

declare(strict_types=1);

namespace Resrap\Component\Grammar;

use Resrap\Component\Grammar\Ast\GrammarFile;
use Resrap\Component\Parser\Parser;

/**
 * A parser specifically designed for handling EBNF (Extended Backus-Naur Form) grammar definitions.
 * This parser utilizes a specific scanner and grammar configuration to interpret and process
 * EBNF input strings, producing a structured grammar representation as output.
 */
final readonly class GrammarParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = Parser::fromGrammar( GrammarSpecification::file(), GrammarScanner::create());
    }

    public function parse(string $input): GrammarFile
    {
        return $this->parser->parse($input);
    }
}
