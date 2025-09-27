# Resrap Components — Parser Utils

This is a small toolbox of parser utilities.
It's not a framework, but it's a good starting point for building your own parser.

## Requirements
- PHP ^8.4

## Installation
```bash
composer require resrap/components
```

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

## Quickstart
See the math example in `examples/Math/main.php`. It is a good starting point.
