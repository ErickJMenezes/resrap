# Resrap — Parser and scanner utils

*WARNING: This package is not intended for serious usage.*

It's not a framework, but it's a good starting point for building your own parser combinator and a regexp-based scanner.

## Requirements
- PHP ^8.4

## Installation
```bash
composer require erickjmenezes/resrap
```

## Examples
Two runnable examples are included.

### Math calculator (toy) example
- [examples/Math/main.php](./examples/Math/main.php) — entry point

Run it:

```bash
composer install
php examples/Math/main.php
```

### JSON Parser example
- [examples/Json/main.php](./examples/Json/main.php)

Run it:

```bash
composer install
php examples/Json/main.php
```

## Quickstart
### 1. Declare the tokens your program needs
```php
enum Token {
    case NUMBER;
    case PLUS;
}
```

### 2. Create a Scanner
```php
use Resrap\Component\Scanner\{ScannerIteratorInterface, ScannerBuilder, Pattern, ScannerToken};

function create_scanner(string $input): ScannerIteratorInterface
{
    return new ScannerBuilder(
        new Pattern('\d+', Token::NUMBER),
        new Pattern('\+', Token::PLUS),
        new Pattern('[\s\r\t\n]++', ScannerToken::SKIP),
    )->build($input);
}
```

### 3. Create a parser for your grammar
```php
use Resrap\Component\Combinator\Parser;

function create_parser(): Parser
{
    // define the grammar and the parser for a number.
    // the number is just a match of a Token::NUMBER.
    $number = new Parser('number')
        ->is(Token::NUMBER)
        ->then(fn(array $m) => (int) $m[0]);   // return first matched token in the sequence

    $expression = new Parser('add_two_numbers')
        // if the expression is just a number, return it
        ->is($number)
        ->then(fn(array $m) => $m[0]);
    // Now, to wrap everything up, we can say that our expression
    // is a number plus another expression
    $expression
        ->is($number, Token::PLUS, $expression)
        ->then(fn(array $m) => $m[0] + $m[2]);

    return $expression;
}
```

### 4. Parse the input
```php
$scanner = create_scanner('1 + 2 + 3');
$parser = create_parser();
echo $parser->apply($scanner); // 6
```

