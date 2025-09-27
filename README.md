# Resrap Components — Parser Utils

A lightweight set of parser-combinator utilities for PHP 8.4. Build parsers using a small, expressive API that composes token matchers and callbacks into reusable, testable combinators.

- Library namespace: `Resrap\Component\*` (autoloaded from `src/`)
- Examples namespace: `Resrap\Examples\*` (autoloaded from `examples/` in dev)

License: MIT (see composer.json)

## Stack and Tooling
- Language: PHP 8.4
- Package manager/build: Composer
- Frameworks: none (plain PHP library)
- Autoloading: PSR-4

## Requirements
- PHP ^8.4
- Composer (for autoloading and installation)

## Installation
If this library is published on Packagist:

```bash
composer require resrap/components
```

For local development of this repository (cloned source):

```bash
composer install
```

Then require Composer’s autoloader in your entry point:

```php
require __DIR__ . '/vendor/autoload.php';
```

## Project Structure
- `src/` — core library
  - `Combinator/`
    - `Parser.php` — define/compose parsing sequences and apply them
    - `PendingSequence.php` — builder returned by `Parser::is()`/`->or()`; completed via `->then()` or `->pass()`
    - `ScannerInterface.php` — contract your scanner must implement
    - `ScannerIterator.php` — small adapter to iterate a `ScannerInterface` and manage position
    - `ParserException.php` — exceptions thrown by the combinators
  - `Scanner/`
    - `ScannerBuilder.php`, `RegexScanner.php`, `Pattern.php`, `NamedRegexp.php`, `ScannerToken.php`, `ScannerException.php` — helpers to build simple regex-based scanners
- `examples/` — runnable examples
  - `Math/` — simple arithmetic expression parser (toy scanner)
    - `main.php` (entry point)
  - `Json/` — JSON parser using the combinators
    - `main.php` (entry point)
- `composer.json` — package metadata and autoload rules
- `vendor/` — Composer dependencies (generated)

## Core Concepts

- Parser (`Resrap\Component\Combinator\Parser`)
  - Define one or more alternative sequences with `Parser::is(...)` and `->or(...)`.
  - Finish each sequence by providing a `->then(function(array $matches) { ... })` callback that transforms matched values to your desired output (e.g., AST nodes). Use `->pass()` to return the raw matches.
  - Apply a parser to a scanner with `->apply(ScannerIterator $iterator)`.

- PendingSequence (`Resrap\Component\Combinator\PendingSequence`)
  - Returned from `::is()` and `->or(...)` and completed by `->then(...)` or `->pass()`.

- ScannerInterface (`Resrap\Component\Combinator\ScannerInterface`)
  - Your lexer/scanner must implement:
    - `lex(): int|UnitEnum` — returns the next token (as an enum case or integer code)
    - `value(): ?string` — returns the current token’s textual value

### Matching semantics in `Parser`
- UnitEnum tokens: If you pass a `UnitEnum` (e.g., an enum case from your token enum), matching uses identity (`===`) against `ScannerIterator::token()`. On match, the current `value()` is pushed into `$matches` and the scanner advances.
- Strings (single characters): If you pass a string like `'+'`, the engine compares `ord($matcher)` to the current token (useful when your scanner emits ASCII codes). If your scanner returns enums instead (as in the example), prefer matching via enum cases.
- Nested/recursive parsers: You can include other `Parser` instances directly in sequences.

## Examples
Two runnable examples are included.

### Math (toy) example
- `examples/Math/main.php` — entry point

Run it from the project root:

```bash
composer install
php examples/Math/main.php
```

### JSON Parser example
Key files:
- `examples/Json/Token.php`, `JsonScanner.php`
- `examples/Json/Parser/JsonParser.php`
- `examples/Json/Ast/*`
- `examples/Json/main.php` — entry point

Run it:

```bash
composer install
php examples/Json/main.php
```

## Quick start (build your own parser with ScannerBuilder)
1. Define your tokens as a `UnitEnum`.

```php
enum Token { case NUMBER; case PLUS; case MINUS; }
```

2. Build a scanner with ScannerBuilder (recommended). It lets you describe token patterns with readable regex and aliases, and it produces a ready-to-use ScannerIterator.

```php
use Resrap\Component\Scanner\ScannerBuilder;
use Resrap\Component\Scanner\Pattern;
use Resrap\Component\Scanner\ScannerToken;

$scannerIt = (new ScannerBuilder(
    // skip whitespace
    new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
    // tokens
    new Pattern('{NUMBER}', Token::NUMBER),
    new Pattern('\+', Token::PLUS),
    new Pattern('-', Token::MINUS),
))
    ->aliases([
        'NUMBER' => '[0-9]+',
    ])
    ->build("12 + 34 - 5");
```

3. Compose your grammar with parsers.

```php
use Resrap\Component\Combinator\Parser;

$number = fn() => Parser::is(Token::NUMBER)
    ->then(fn(array $m) => intval($m[0]));

$operator = fn() => Parser::is(Token::PLUS)
    ->then(fn(array $m) => $m[0])
    ->or(Token::MINUS)->then(fn(array $m) => $m[0]);

$expr = function () use ($number, $operator, &$expr) {
    return Parser::is($number)
        ->then(fn(array $m) => $m[0])
        ->or($number, $operator, $expr)
        ->then(fn(array $m) => [$m[0], $m[1], $m[2]]);
};
```

4. Parse.

```php
$ast = $expr()->apply($scannerIt);
```

Note: For a real-world example using ScannerBuilder, see examples/Json/JsonScanner.php, which defines STRING and NUMBER via aliases and handles whitespace skipping.

Advanced: You can still implement ScannerInterface manually if you have special lexing needs, but ScannerBuilder should cover most cases and avoids hand-rolled scanner bloat.

## Entry points and run commands
- JSON example: `php examples/Json/main.php`
- Math example: `php examples/Math/main.php`

Prerequisite: `composer install` to generate `vendor/autoload.php`.

## Scripts
- Composer scripts: none defined in composer.json. TODO: Add scripts for running examples and coding standards if desired.

## Tests
- There is currently no tests/ directory in this repository.
- TODO: Add PHPUnit (or Pest) configuration and tests for parsers and scanners.

## Error handling
- If no sequence matches at the current position, `Parser::apply()` throws `Resrap\Component\Combinator\ParserException`.

## License
MIT. See `composer.json` for the declared license.
