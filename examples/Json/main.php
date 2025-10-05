<?php

declare(strict_types=1);

use Resrap\Examples\Json\JsonParser;
use Resrap\Examples\Json\JsonScanner;

require __DIR__ . '/../../vendor/autoload.php';

$json = '{"name":"Alice", "age":30,"isMember":true,"favorites":["apples", "bananas"],"meta":{"height":1.68,"active":false,"tags":null}}';

$scanner = JsonScanner::build();
$parser = new JsonParser($scanner);
$result = $parser->parse($json);

var_dump($result->pairs[3]->value->items[0]->value); // "apples"
