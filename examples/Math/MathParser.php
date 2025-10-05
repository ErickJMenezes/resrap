<?php

namespace Resrap\Examples\Math;

use Resrap\Component\Parser\Parser;
use Resrap\Component\Scanner\Scanner;

final class MathParser
{
    private const array RULES = array (
  0 =>
  array (
    0 => 'calculator',
    1 =>
    array (
      0 => 'expression',
    ),
  ),
  1 =>
  array (
    0 => 'expression',
    1 =>
    array (
      0 => 'number',
    ),
  ),
  2 =>
  array (
    0 => 'expression',
    1 =>
    array (
      0 => 'number',
      1 => 'operator',
      2 => 'expression',
    ),
  ),
  3 =>
  array (
    0 => 'number',
    1 =>
    array (
      0 => 'NUMBER',
    ),
  ),
  4 =>
  array (
    0 => 'operator',
    1 =>
    array (
      0 => 'PLUS',
    ),
  ),
  5 =>
  array (
    0 => 'operator',
    1 =>
    array (
      0 => 'MINUS',
    ),
  ),
  6 =>
  array (
    0 => 'operator',
    1 =>
    array (
      0 => 'TIMES',
    ),
  ),
  7 =>
  array (
    0 => 'operator',
    1 =>
    array (
      0 => 'DIV',
    ),
  ),
);
    private const array ACTIONS = array (
  0 =>
  array (
    'NUMBER' =>
    array (
      0 => 0,
      1 => 3,
    ),
  ),
  1 =>
  array (
    '$' =>
    array (
      0 => 2,
      1 => 0,
    ),
  ),
  2 =>
  array (
    '$' =>
    array (
      0 => 1,
      1 => 1,
    ),
    'PLUS' =>
    array (
      0 => 0,
      1 => 5,
    ),
    'MINUS' =>
    array (
      0 => 0,
      1 => 6,
    ),
    'TIMES' =>
    array (
      0 => 0,
      1 => 7,
    ),
    'DIV' =>
    array (
      0 => 0,
      1 => 8,
    ),
  ),
  3 =>
  array (
    '$' =>
    array (
      0 => 1,
      1 => 3,
    ),
    'PLUS' =>
    array (
      0 => 1,
      1 => 3,
    ),
    'MINUS' =>
    array (
      0 => 1,
      1 => 3,
    ),
    'TIMES' =>
    array (
      0 => 1,
      1 => 3,
    ),
    'DIV' =>
    array (
      0 => 1,
      1 => 3,
    ),
  ),
  4 =>
  array (
    'NUMBER' =>
    array (
      0 => 0,
      1 => 3,
    ),
  ),
  5 =>
  array (
    'NUMBER' =>
    array (
      0 => 1,
      1 => 4,
    ),
  ),
  6 =>
  array (
    'NUMBER' =>
    array (
      0 => 1,
      1 => 5,
    ),
  ),
  7 =>
  array (
    'NUMBER' =>
    array (
      0 => 1,
      1 => 6,
    ),
  ),
  8 =>
  array (
    'NUMBER' =>
    array (
      0 => 1,
      1 => 7,
    ),
  ),
  9 =>
  array (
    '$' =>
    array (
      0 => 1,
      1 => 2,
    ),
  ),
);
    private const array GOTO = array (
  0 =>
  array (
    'expression' => 1,
    'number' => 2,
  ),
  2 =>
  array (
    'operator' => 4,
  ),
  4 =>
  array (
    'expression' => 9,
    'number' => 2,
  ),
  1 =>
  array (
  ),
  3 =>
  array (
  ),
  5 =>
  array (
  ),
  6 =>
  array (
  ),
  7 =>
  array (
  ),
  8 =>
  array (
  ),
  9 =>
  array (
  ),
);
    private Parser $parser;
    public function __construct(Scanner $scanner)
    {
        $callbacks = [];
        $callbacks[0] = function (array $m) { return eval("return {$m[0]};"); };
        $callbacks[1] = function (array $m) { return $m[0]; };
        $callbacks[2] = function (array $m) { return "{$m[0]} {$m[1]} {$m[2]}"; };
        $callbacks[3] = function (array $m) { return $m[0]; };
        $callbacks[4] = function (array $m) { return $m[0]; };
        $callbacks[5] = function (array $m) { return $m[0]; };
        $callbacks[6] = function (array $m) { return $m[0]; };
        $callbacks[7] = function (array $m) { return $m[0]; };
        $this->parser = new Parser(self::ACTIONS, self::GOTO, $callbacks, self::RULES, $scanner);
    }
    public function parse(string $input): mixed
    {
        return $this->parser->parse($input);
    }
}
