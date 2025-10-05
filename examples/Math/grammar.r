%class MathParser;

%use Resrap\Examples\Math\MathToken;

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
