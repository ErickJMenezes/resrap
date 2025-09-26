# Resrap Components — Parser Utils

A lightweight set of parser-combinator utilities for PHP 8.4. Build parsers using a small, expressive API that composes token matchers and callbacks into reusable, testable combinators.

Namespaces:
- Library: `Resrap\Component\*` (autoloaded from `src/`)
- Examples: `Resrap\Examples\*` (autoloaded from `examples/` in dev)

License: MIT

## Requirements
- PHP ^8.4
- Composer (for autoloading and installation)

## Installation
If published on Packagist:

```bash
composer require resrap/components
```

For local development (this repository checked out locally):

```bash
composer install
```

Then require Composer’s autoloader in your entry point:

```php
require __DIR__ . '/vendor/autoload.php';
```

## Core Concepts

- `Parser` (`Resrap\Component\Combinator\Parser`)
  - Define one or more alternative sequences with `::is(...)` and `->or(...)`.
  - Finish each sequence by providing a `->then(function(array $matches) { ... })` callback that transforms matched values to your desired output (e.g., AST nodes). Use `->pass()` to return the raw matches.
  - Apply a parser to a scanner with `->apply(ScannerInterface $scanner)`.

- `PendingSequence` (`Resrap\Component\Impl\PendingSequence`)
  - Returned from `::is()` and `->or(...)` and completed by `->then(...)` or `->pass()`.

- `ScannerInterface` (`Resrap\Component\Combinator\ScannerInterface`)
  - Your lexer/scanner must implement:
    - `lex(): int|UnitEnum` — returns the next token (as an `enum` case or integer code).
    - `value(): ?string` — returns the current token’s textual value.

### Matching semantics in `Parser`
- UnitEnum tokens: If you pass a `UnitEnum` (e.g. an enum case from your token enum), matching uses identity (`===`) against `ScannerInterface::lex()`. On match, the current `value()` is pushed into `$matches` and the scanner advances.
- Strings (single characters): If you pass a string like `'+'`, the engine compares `ord($matcher)` to `ScannerInterface::lex()`. This is useful when your scanner emits ASCII codes for punctuation. If your scanner returns enums instead (as in the example), prefer matching via enum cases.
- Nested/recursive parsers: You can include other `Parser` instances directly in sequences.

## Example: Arithmetic expression parser
A minimal example is provided under `examples/Math/`. It demonstrates parsing a simple arithmetic expression into an AST using enums for tokens and a fake scanner.

Key files:
- `examples/Math/Token.php` — token enum (e.g., `NUMBER`, `PLUS`, `MINUS`, ...)
- `examples/Math/FakeScanner.php` — toy implementation of `ScannerInterface`
- `examples/Math/Ast/*` — simple AST node classes
- `examples/Math/Parser/MathExpressionParser.php` — parsers composing the parser
- `examples/Math/main.php` — runnable example

### Parser definition (excerpt)
From `examples/Math/Parser/MathExpressionParser.php`:

```php
use Resrap\Component\Combinator\Parser;
use Resrap\Examples\Math\Ast\MathExpression;
use Resrap\Examples\Math\Ast\MathOperator;
use Resrap\Examples\Math\Ast\Number;
use Resrap\Examples\Math\Token;

final class MathExpressionParser
{
    public static function expression(): Parser
    {
        return Parser::is(self::number())
            ->then(fn(array $m) => $m[0])
            // `self::expression(...)` self-references this parser for recursion and avoid infinity loop
            ->or(self::number(), self::operator(), self::expression(...))
            ->then(fn(array $m) => new MathExpression([$m[0], $m[1], $m[2]]));
    }

    public static function number(): Parser
    {
        return Parser::is(Token::NUMBER)
            ->then(fn(array $m) => new Number($m[0]));
    }

    public static function operator(): Parser
    {
        $whenMatches = fn(array $m) => new MathOperator($m[0]);
        return Parser::is(Token::PLUS)->then($whenMatches)
            ->or(Token::MINUS)->then($whenMatches)
            ->or(Token::MULTIPLY)->then($whenMatches)
            ->or(Token::DIVIDE)->then($whenMatches);
    }
}
```

### Running the example
Install dependencies and run the example script. From the project root:

```bash
composer install
php examples/Math/main.php
```

This will parse a hard-coded token stream from `FakeScanner` and `var_dump` an AST composed of `Number` and `MathExpression` nodes.

## Quick start (building your own parser)
1. Define your tokens as a `UnitEnum` (recommended) or integers if you prefer ASCII codes for punctuation.

```php
enum Token { case NUMBER; case PLUS; case MINUS; case MULTIPLY; case DIVIDE; }
```

2. Implement `ScannerInterface` to emit your tokens and values.

```php
use Resrap\Component\Combinator\ScannerInterface;

final class MyScanner implements ScannerInterface {
    // ... keep track of position, tokens, and values
    public function lex(): int|UnitEnum { /* lext return next token */ }
    public function value(): ?string { /* return last parsed token text */ }
}
```

3. Compose your grammar with parsers.

```php
use Resrap\Component\Combinator\Parser;

$number = fn() => Parser::is(Token::NUMBER)
    ->then(fn($m) => intval($m[0]));

$operator = fn() => Parser::is(Token::PLUS)
    ->then(fn($m) => $m[0])
    ->or(Token::MINUS)->then(fn($m) => $m[0]);

$expr = function () use ($number, $operator, &$expr) {
    return Parser::is($number())
        ->then(fn($m) => $m[0])
        ->or($number(), $operator(), $expr(...))
        ->then(fn($m) => [$m[0], $m[1], $m[2]])
};
```

4. Parse.

```php
use Resrap\Component\Combinator\ScannerIterator;

$scanner = new MyScanner(/* your input */);
$ast = $expr()->apply(new ScannerIterator($scanner));
```

## Error handling
- If no sequence matches at the current position, `Parser::apply()` throws `RuntimeException` with the unexpected value and the scanner position.

## Examples
- `examples/` — runnable examples and demonstration code
  - `Math/` Simple math expression parser
  - `Json/` JSON Parser

## License
MIT. See `composer.json` for the declared license.
