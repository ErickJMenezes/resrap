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
use Resrap\Component\Scanner\{ScannerInterface, ScannerBuilder, Pattern, ScannerToken};

function create_scanner(): ScannerIteratorInterface
{
    return new ScannerBuilder(
        new Pattern('\d+', Token::NUMBER),
        new Pattern('\+', Token::PLUS),
        new Pattern('[\s\r\t\n]++', ScannerToken::SKIP),
    )->build();
}
```

### 3. Create grammar rules to parse your tokens

```php
use Resrap\Component\Parser\GrammarRule;

function create_grammar(): GrammarRule
{
    // number := T_NUMBER
    $number = new GrammarRule('number')
        ->is(Token::NUMBER)
        // return the value of the first matched token
        ->then(fn(array $m) => (int) $m[0]);

    // add := number | number T_PLUS add
    $add = new GrammarRule('add')
        // number
        ->is($number)
        ->then(fn(array $m) => $m[0]);
    $add
        // number T_PLUS add
        ->is($number, Token::PLUS, $add)
        ->then(fn(array $m) => $m[0] + $m[2]);

    return $add;
}
```

### 4. Parse the input

```php
use Resrap\Component\Parser\Parser;

$scanner = create_scanner();
$grammar = create_grammar();
$parser = new Parser($scanner, $grammar);
echo $parser->parse('1 + 2 + 3'); // 6
```

