<?php

namespace Resrap\Tests\Unit\Scanner;

use Resrap\Component\Scanner\InputBuffer;
use Resrap\Component\Scanner\ManualPattern;
use Resrap\Component\Scanner\StatefulRegexScanner;
use Resrap\Component\Scanner\ScannerToken;
use Resrap\Component\Scanner\State;
use Resrap\Component\Scanner\StateTransition;

enum TestToken
{
    case Foo;
    case Change;
    case Bar;
    case Back;
}

test('must match a string and eof', function () {
    $scanner = new StatefulRegexScanner(new State(
        'test',
        ['/^foo/xs' => fn() => TestToken::Foo],
    ), []);
    $scanner->setInput(new InputBuffer('foo'));
    expect($scanner->lex())
        ->toBe(TestToken::Foo)
        ->and($scanner->value())
        ->toBe('foo')
        ->and($scanner->lex())
        ->toBe(ScannerToken::EOF);
});

test('must be able to change states', function () {
    $subject = new InputBuffer('foochangebarbackfoo');
    $state1 = new State(
        'first',
        [
            '/^foo/xs' => fn() => TestToken::Foo,
            '/^change/xs' => fn() => TestToken::Change,
            '/^back/xs' => fn() => TestToken::Back,
        ],
        [
            new StateTransition([TestToken::Change], 'second'),
        ]
    );
    $state2 = new State(
        'second',
        [
            '/^bar/xs' => fn() => TestToken::Bar,
            '/^change/xs' => fn() => TestToken::Change,
            '/^back/xs' => fn() => TestToken::Back,
        ],
        [
            new StateTransition([TestToken::Back], 'first'),
        ]
    );
    $scanner = new StatefulRegexScanner($state1, [
        $state1->name => $state1,
        $state2->name => $state2,
    ]);
    $scanner->setInput($subject);
    expect($scanner->lex())
        ->toBe(TestToken::Foo)
        ->and($scanner->value())
        ->toBe('foo')
        ->and($scanner->lex())
        ->toBe(TestToken::Change)
        ->and($scanner->value())
        ->toBe('change')
        ->and($scanner->lex())
        ->toBe(TestToken::Bar)
        ->and($scanner->value())
        ->toBe('bar')
        ->and($scanner->lex())
        ->toBe(TestToken::Back)
        ->and($scanner->value())
        ->toBe('back')
        ->and($scanner->lex())
        ->toBe(TestToken::Foo)
        ->and($scanner->value())
        ->toBe('foo')
    ;
});

test('pattern manual', function () {
    $scanner = new StatefulRegexScanner(
        new State(
            'test',
            [
                '__manual_0' => new ManualPattern(function (string $buffer) {
                    return [TestToken::Foo, 3, 'foo'];
                }),
            ]
        ),
        [],
    );
    $input = new InputBuffer('foo');
    $scanner->setInput($input);
    expect($scanner->lex())
        ->toBe(TestToken::Foo)
        ->and($scanner->value())
        ->toBe('foo')
        ->and($scanner->lex())
        ->toBe(ScannerToken::EOF)
        ->and($input->content)
        ->toBeEmpty();
});
