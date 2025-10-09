<?php

namespace Resrap\Tests\Unit\Scanner;

use LogicException;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\Scanner;
use Resrap\Component\Scanner\StatefulScannerBuilder;

test('it must fail if no states are defined', function () {
    expect(fn() => new StatefulScannerBuilder()->build())
        ->toThrow(LogicException::class, 'At least one state must be defined.');
});

test('it must fail if the initial state name is not a valid state', function () {
    expect(fn() => new StatefulScannerBuilder()
        ->state('invalid', [new Pattern('foo', fn() => null)], [])
        ->setInitialState('foo')
        ->build()
    )
        ->toThrow(LogicException::class, "There is no state named 'foo'.");
});

test('it must fail if the state has no patterns', function () {
    expect(fn() => new StatefulScannerBuilder()
        ->state('invalid', [], [])
        ->build()
    )
        ->toThrow(LogicException::class, 'At least one pattern must be defined.');
});

test('must successfully build a scanner', function () {
    $scanner = new StatefulScannerBuilder()
        ->state('valid', [new Pattern('foo', fn() => null)], [])
        ->build();
    expect($scanner)
        ->toBeInstanceOf(Scanner::class);
});
