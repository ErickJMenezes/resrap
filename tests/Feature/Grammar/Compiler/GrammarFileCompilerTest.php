<?php

namespace Resrap\Tests\Feature\Grammar\Compiler;

use Resrap\Component\Grammar\GrammarFileCompiler;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\ScannerToken;

enum TestToken
{
    case Add;
    case Bet;
    case Plus;
}

test('must compile grammar', function () {
    $file = '
    %class MyGrammar;
    %use Resrap\Tests\Feature\Grammar\Compiler\TestToken;
    %start expr;
    
    expr := TestToken::Add TestToken::Plus TestToken::Bet { return [$1, $2, $3]; }
          ;
    ';

    $scanner = new ScannerBuilder(
        new Pattern('Add', fn() => TestToken::Add),
        new Pattern('Bet', fn() => TestToken::Bet),
        new Pattern('\+', fn($value) => TestToken::Plus),
        new Pattern('[\s\t\n\r]+', fn() => ScannerToken::SKIP),
    )->build();

    $fileCompiler = new GrammarFileCompiler();
    $code = $fileCompiler->compile($file);
    eval($code);
    $parser = new \MyGrammar($scanner);
    $result = $parser->parse('Add + Bet');

    expect($result)->toBe(['Add', '+', 'Bet']);
});
