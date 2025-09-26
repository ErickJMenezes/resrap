<?php

declare(strict_types=1);

use Resrap\Examples\Json\JsonScanner;
use Resrap\Examples\Json\Parser\JsonParser;

require __DIR__ . '/../../vendor/autoload.php';

$json = $argv[1] ?? '{"name":"Alice","age":30,"isMember":true,"favorites":["apples", "bananas"],"meta":{"height":1.68,"active":false,"tags":null}}';

$scanner = new JsonScanner($json);
$parser = JsonParser::value();
$result = $parser->apply($scanner);
var_dump($result);
