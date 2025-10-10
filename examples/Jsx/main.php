<?php

require __DIR__ . '/../../vendor/autoload.php';

use Resrap\Examples\Jsx\{JsxScanner, JsxParser};

$jsx = <<<'JSX'
const element = <div className="container">
    <h1>Hello {name}!</h1>
    <Button onClick={handleClick}>Click me</Button>
</div>;
JSX;

$scanner = JsxScanner::build();
$parser = new JsxParser($scanner);

try {
    $ast = $parser->parse($jsx);
    print_r($ast);
    echo "Reconstructed: \n\n{$ast->toString()}\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
