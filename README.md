# Resrap — Parser and scanner utils

_WARNING: This package is not intended for serious usage._

It's not a framework, but it's a good starting point for building your own parser and a regexp-based scanner.

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
enum MathToken
{
    case NUMBER;
    case PLUS;
    case MINUS;
    case TIMES;
    case DIV;
}
```

### 2. Create a Scanner

```php
use Resrap\Component\Scanner\{Scanner, ScannerBuilder, Pattern, ScannerToken};

function create_scanner(): Scanner
{
    return new ScannerBuilder(
        // skip whitespace
        new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
        // tokens
        new Pattern('{NUMBER}', MathToken::NUMBER),
        new Pattern('\+', MathToken::PLUS),
        new Pattern('-', MathToken::MINUS),
        new Pattern('\*', MathToken::TIMES),
        new Pattern('\\/', MathToken::DIV),
    )
        ->aliases([
            'NUMBER' => '[0-9]+',
        ])
        ->build();
}
```

### 3. Create grammar rules to parse your tokens

```
%class MathParser;

%use Whatever\Namespace\Of\MathToken;

%start calculator;

number := MathToken::NUMBER { return $1; }
        ;

operator := MathToken::PLUS     { return $1; }
          | MathToken::MINUS    { return $1; }
          | MathToken::TIMES    { return $1; }
          | MathToken::DIV      { return $1; }
          ;

expression := number                     { return $1; }
            | number operator expression { return "{$1} {$2} {$3}"; }
            ;

calculator := expression { return eval("return {$1};"); }
            ;
```

### 4. Generate the parser

```bash
php bin/resrap compile my-grammar.rr > MathParser.php
```

### 5. Use the parser

```php
$parser = new MathParser(create_scanner());

echo $parser->parse('1 + 2 + 3'); // 6
```
