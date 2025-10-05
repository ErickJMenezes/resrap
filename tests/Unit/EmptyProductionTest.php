<?php

namespace Resrap\Tests\Unit;

use Resrap\Component\Parser\GrammarRule;
use Resrap\Component\Parser\Parser;
use Resrap\Component\Scanner\Position;
use Resrap\Component\Scanner\Scanner;
use Resrap\Component\Scanner\ScannerToken;
use UnitEnum;

enum TestToken
{
    case A;
    case B;
}

class SimpleScanner implements Scanner
{
    private array $tokens = [];
    private int $index = 0;

    public function setInput(string $input): void
    {
        $this->tokens = match ($input) {
            'a' => [TestToken::A],
            'b' => [TestToken::B],
            '' => [],
            default => throw new Exception("Unknown input: $input")
        };
        $this->index = 0;
    }

    public function lex(): UnitEnum
    {
        if ($this->index >= count($this->tokens)) {
            return ScannerToken::EOF;
        }
        return $this->tokens[$this->index++];
    }

    public function value(): ?string
    {
        return null;
    }

    public function position(): Position
    {
        return new Position(0, 1, 1);
    }

    public function lastTokenPosition(): Position
    {
        return new Position(0, 1, 1);
    }
}

test('empty production - case 1: empty input', function () {
    // Grammar: S -> ε | A (ORDEM IMPORTA!)
    $s = new GrammarRule('S');
    $s
        ->is()  // ← VAZIA PRIMEIRO!
    ->then(fn(array $m) => 'empty');
    $s
        ->is(TestToken::A)
        ->then(fn(array $m) => 'got A');

    $scanner = new SimpleScanner();
    $parser = Parser::fromGrammar($s, $scanner);

    $result = $parser->parse('');

    expect($result)->toBe('empty');
});

test('empty production - case 2: with token', function () {
    // Grammar: S -> ε | A (ORDEM IMPORTA!)
    $s = new GrammarRule('S');
    $s
        ->is()  // ← VAZIA PRIMEIRO!
    ->then(fn(array $m) => 'empty');
    $s
        ->is(TestToken::A)
        ->then(fn(array $m) => 'got A');

    $scanner = new SimpleScanner();
    $parser = Parser::fromGrammar($s, $scanner);

    $result = $parser->parse('a');

    expect($result)->toBe('got A');
});

test('empty production - case 3: optional in middle', function () {
    // Grammar: S -> A OptB
    //          OptB -> B | ε

    $optB = new GrammarRule('OptB');
    $optB
        ->is()
        ->then(fn(array $m) => 'no B');
    $optB
        ->is(TestToken::B)
        ->then(fn(array $m) => 'has B');

    $s = new GrammarRule('S');
    $s
        ->is(TestToken::A, $optB)
        ->then(fn(array $m) => "A + {$m[1]}");

    $scanner = new SimpleScanner();
    $parser = Parser::fromGrammar($s, $scanner);

    // Test without B
    $result = $parser->parse('a');
    expect($result)->toBe('A + no B');
});
