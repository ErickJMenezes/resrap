<?php

use Resrap\Examples\Math\FakeScanner;
use Resrap\Examples\Math\Parser\MathExpressionParser;

require __DIR__.'/../../vendor/autoload.php';

$scanner = new FakeScanner();
$parser = MathExpressionParser::expression();
$result = $parser->apply($scanner);
var_dump($result);
