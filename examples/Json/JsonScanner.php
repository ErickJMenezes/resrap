<?php

declare(strict_types = 1);

namespace Resrap\Examples\Json;

use Resrap\Component\Spec\ScannerInterface;
use RuntimeException;
use UnitEnum;

final class JsonScanner implements ScannerInterface
{
    /** @var list<Token> */
    private array $tokens = [];

    /** @var list<string> */
    private array $values = [];

    private int $pos = -1;

    public function __construct(private readonly string $input)
    {
        $this->tokenize();
    }

    private function tokenize(): void
    {
        $s = $this->input;
        $len = strlen($s);
        $i = 0;

        while ($i < $len) {
            $ch = $s[$i];

            // whitespace
            if ($ch === " " || $ch === "\t" || $ch === "\n" || $ch === "\r") {
                $i++;
                continue;
            }

            // punctuation
            switch ($ch) {
                case '{':
                    $this->push(Token::LBRACE, '{');
                    $i++;
                    continue 2;
                case '}':
                    $this->push(Token::RBRACE, '}');
                    $i++;
                    continue 2;
                case '[':
                    $this->push(Token::LBRACKET, '[');
                    $i++;
                    continue 2;
                case ']':
                    $this->push(Token::RBRACKET, ']');
                    $i++;
                    continue 2;
                case ':':
                    $this->push(Token::COLON, ':');
                    $i++;
                    continue 2;
                case ',':
                    $this->push(Token::COMMA, ',');
                    $i++;
                    continue 2;
            }

            // string
            if ($ch === '"') {
                [$decoded, $newI] = $this->readString($s, $i);
                $this->push(Token::STRING, $decoded);
                $i = $newI;
                continue;
            }

            // number
            if ($ch === '-' || ($ch >= '0' && $ch <= '9')) {
                [$num, $newI] = $this->readNumber($s, $i);
                $this->push(Token::NUMBER, $num);
                $i = $newI;
                continue;
            }

            // literals: true / false / null
            if ($ch === 't' && str_starts_with(substr($s, $i), 'true')) {
                $this->push(Token::TRUE, 'true');
                $i += 4;
                continue;
            }
            if ($ch === 'f' && str_starts_with(substr($s, $i), 'false')) {
                $this->push(Token::FALSE, 'false');
                $i += 5;
                continue;
            }
            if ($ch === 'n' && str_starts_with(substr($s, $i), 'null')) {
                $this->push(Token::NULL, 'null');
                $i += 4;
                continue;
            }

            throw new RuntimeException("Invalid character '$ch' at position $i");
        }
    }

    private function push(Token $t, string $value): void
    {
        $this->tokens[] = $t;
        $this->values[] = $value;
    }

    /**
     * @return array{0:string,1:int}
     */
    private function readString(string $s, int $i): array
    {
        // assumes $s[$i] === '"'
        $i++; // skip opening quote
        $start = $i;
        $raw = '';
        $len = strlen($s);
        while ($i < $len) {
            $ch = $s[$i];
            if ($ch === '"') {
                // decode JSON escapes by leveraging json_decode
                $decoded = json_decode('"'.$raw.'"');
                if ($decoded === null && $raw !== 'null') {
                    // If decoding failed and it's not actually the literal null, report error
                    $err = json_last_error_msg();
                    throw new RuntimeException("Invalid string escape sequence near position $i: $err");
                }
                $i++; // consume closing quote
                return [$decoded ?? '', $i];
            }
            if ($ch === "\\") {
                // keep escape and the next char(s) in raw; json_decode will process them
                $i++;
                if ($i >= $len) {
                    throw new RuntimeException('Unterminated string literal');
                }
                $next = $s[$i];
                if ($next === 'u') {
                    // Expect 4 hex digits
                    $raw .= "\\u";
                    $i++;
                    for ($k = 0; $k < 4; $k++) {
                        if ($i >= $len) {
                            throw new RuntimeException('Incomplete unicode escape');
                        }
                        $hex = $s[$i];
                        $isHex = ($hex >= '0' && $hex <= '9') || ($hex >= 'a' && $hex <= 'f') || ($hex >= 'A' && $hex <= 'F');
                        if (!$isHex) {
                            throw new RuntimeException('Invalid unicode escape');
                        }
                        $raw .= $hex;
                        $i++;
                    }
                    continue;
                }
                // simple escape
                $raw .= "\\".$next;
                $i++;
                continue;
            }
            // regular char
            $raw .= $ch;
            $i++;
        }
        throw new RuntimeException('Unterminated string literal');
    }

    /**
     * @return array{0:string,1:int}
     */
    private function readNumber(string $s, int $i): array
    {
        $sub = substr($s, $i);
        if (!preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+\-]?\d+)?/A', $sub, $m)) {
            throw new RuntimeException("Invalid number at position $i");
        }
        $lexeme = $m[0];
        $i += strlen($lexeme);
        return [$lexeme, $i];
    }

    public function lex(): int|UnitEnum
    {
        $this->pos++;
        if ($this->pos >= count($this->tokens)) {
            $this->pos--;
            return ScannerInterface::EOF;
        }
        return $this->tokens[$this->pos];
    }

    public function value(): ?string
    {
        return $this->values[$this->pos] ?? null;
    }
}
