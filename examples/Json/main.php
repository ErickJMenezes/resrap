<?php

declare(strict_types=1);

use Resrap\Component\Parser\Parser;
use Resrap\Examples\Json\JsonScanner;
use Resrap\Examples\Json\Parser\JsonGrammar;

require __DIR__ . '/../../vendor/autoload.php';

$json = '{"name":"Alice","age":30,"isMember":true,"favorites":["apples", "bananas"],"meta":{"height":1.68,"active":false,"tags":null}}';

$scanner = JsonScanner::build($json);
$grammar = JsonGrammar::value();
$parser = new Parser($scanner, $grammar);
$result = $parser->parse();

var_dump($result->pairs[3]->value->items[0]->value); // "apples"
