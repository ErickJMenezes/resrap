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

- `Combinator` (`Resrap\Component\Impl\Combinator`)
  - Define a named parser using `Combinator::is('name')`.
  - Add one or more alternative sequences with `->or(...)`.
  - Finish each sequence by providing a `->then(function(array $matches) { ... })` callback that transforms matched values to your desired output (e.g., AST nodes). Use `->pass()` to return the raw matches.
  - Apply a combinator to a scanner with `->apply(ScannerInterface $scanner)`.

- `PendingSequence` (`Resrap\Component\Impl\PendingSequence`)
  - Returned from `->or(...)` and completed by `->then(...)` or `->pass()`.

- `ScannerInterface` (`Resrap\Component\Spec\ScannerInterface`)
  - Your lexer/scanner must implement:
    - `lex(): int|UnitEnum` — returns the current token (as an `enum` case or integer code).
    - `value(): ?string` — returns the current token’s textual value.
    - `advance(): void` — move to the next token.
    - `goto(int $index): void` — restore the cursor position.
    - `index(): int` — current position.
    - `eof(): bool` — end-of-stream check.

### Matching semantics in `Combinator`
- UnitEnum tokens: If you pass a `UnitEnum` (e.g. an enum case from your token enum), matching uses identity (`===`) against `ScannerInterface::lex()`. On match, the current `value()` is pushed into `$matches` and the scanner advances.
- Strings (single characters): If you pass a string like `'+'`, the engine compares `ord($matcher)` to `ScannerInterface::lex()`. This is useful when your scanner emits ASCII codes for punctuation. If your scanner returns enums instead (as in the example), prefer matching via enum cases.
- Nested/recursive combinators: You can include other `Combinator` instances directly in sequences. To self-reference the current combinator (for recursion), pass the placeholder string `":<name>:"` (e.g., `":math_expression:"`). The engine replaces it with the current combinator instance.

## Example: Arithmetic expression parser
A minimal example is provided under `examples/Math/`. It demonstrates parsing a simple arithmetic expression into an AST using enums for tokens and a fake scanner.

Key files:
- `examples/Math/Token.php` — token enum (e.g., `NUMBER`, `PLUS`, `MINUS`, ...)
- `examples/Math/FakeScanner.php` — toy implementation of `ScannerInterface`
- `examples/Math/Ast/*` — simple AST node classes
- `examples/Math/Parser/MathExpressionParser.php` — combinators composing the parser
- `examples/Math/main.php` — runnable example

### Parser definition (excerpt)
From `examples/Math/Parser/MathExpressionParser.php`:

```php
use Resrap\Component\Impl\Combinator;
use Resrap\Examples\Math\Ast\MathExpression;
use Resrap\Examples\Math\Ast\MathOperator;
use Resrap\Examples\Math\Ast\Number;
use Resrap\Examples\Math\Token;

final class MathExpressionParser
{
    public static function expression(): Combinator
    {
        return Combinator::is('math_expression')
            ->or(self::number())
            ->then(fn(array $m) => $m[0])
            // `":math_expression:"` self-references this combinator for recursion
            ->or(self::number(), self::operator(), ':math_expression:')
            ->then(fn(array $m) => new MathExpression([$m[0], $m[1], $m[2]]));
    }

    public static function number(): Combinator
    {
        return Combinator::is('math_number')
            ->or(Token::NUMBER)
            ->then(fn(array $m) => new Number($m[0]));
    }

    public static function operator(): Combinator
    {
        $whenMatches = fn(array $m) => new MathOperator($m[0]);
        return Combinator::is('math_operator')
            ->or(Token::PLUS)->then($whenMatches)
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
use Resrap\Component\Spec\ScannerInterface;
use UnitEnum;

final class MyScanner implements ScannerInterface {
    // ... keep track of position, tokens, and values
    public function lex(): int|UnitEnum { /* return current token */ }
    public function value(): ?string { /* return current text */ }
    public function advance(): void { /* move forward */ }
    public function goto(int $index): void { /* backtrack */ }
    public function index(): int { /* position */ }
    public function eof(): bool { /* end */ }
}
```

3. Compose your grammar with combinators.

```php
use Resrap\Component\Impl\Combinator;

$number = fn() => Combinator::is('number')
    ->or(Token::NUMBER)
    ->then(fn($m) => intval($m[0]));

$operator = fn() => Combinator::is('operator')
    ->or(Token::PLUS)->then(fn($m) => $m[0])
    ->or(Token::MINUS)->then(fn($m) => $m[0]);

$expr = fn() => Combinator::is('expr')
    ->or($number())
    ->then(fn($m) => $m[0])
    ->or($number(), $operator(), ':expr:')
    ->then(fn($m) => [$m[0], $m[1], $m[2]]);
```

4. Parse.

```php
$scanner = new MyScanner(/* your input */);
$ast = $expr()->apply($scanner);
```

## Error handling
- If no sequence matches at the current position, `Combinator::apply()` throws `RuntimeException` with the unexpected value and the scanner position.
- You can use `ScannerInterface::goto()` and the library’s internal backtracking to try next alternatives; this happens automatically for each `or(...)` branch.

## Project structure
- `src/` — library source
  - `Impl/Combinator.php`
  - `Impl/PendingSequence.php`
  - `Spec/CombinatorInterface.php`
  - `Spec/ScannerInterface.php`
- `examples/` — runnable examples and demonstration code
  - `Math/`
    - `Token.php`
    - `FakeScanner.php`
    - `Ast/`
    - `Parser/`
    - `main.php`

## Contributing
- Issues and PRs are welcome.
- Please keep the public API small and focused.
- Add or update examples to demonstrate new capabilities.

## License
MIT. See `composer.json` for the declared license.
