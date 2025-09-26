<?php

declare(strict_types=1);

use Resrap\Component\Impl\ScannerIterator;
use Resrap\Examples\Json\JsonScanner;
use Resrap\Examples\Json\Parser\JsonParser;

require __DIR__ . '/../../vendor/autoload.php';

$json = $argv[1] ?? '{"name":"Alice","age":30,"isMember":true,"favorites":["apples", "bananas"],"meta":{"height":1.68,"active":false,"tags":null}}';

$result = JsonParser::value()
    ->apply(new ScannerIterator(new JsonScanner($json)));
var_dump($result->pairs[3]->value->items[0]->value); // "apples"
