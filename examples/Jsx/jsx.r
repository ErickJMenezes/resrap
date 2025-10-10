%class JsxParser;
%use Resrap\Examples\Jsx\JsxToken;
%use Resrap\Examples\Jsx\Ast\Program;
%use Resrap\Examples\Jsx\Ast\ConstDeclaration;
%use Resrap\Examples\Jsx\Ast\JsxElement;
%use Resrap\Examples\Jsx\Ast\JsxAttribute;
%use Resrap\Examples\Jsx\Ast\TextNode;
%use Resrap\Examples\Jsx\Ast\Interpolation;
%use Resrap\Examples\Jsx\Ast\Identifier;
%use Resrap\Examples\Jsx\Ast\NumberLiteral;
%start program;

program := statements { return new Program($1); }
         ;

statements := statement statements { return [$1, ...$2]; }
            |                      { return []; }
            ;

statement := constDeclaration { return $1; }
           | expression        { return $1; }
           ;

constDeclaration := JsxToken::CONST 
                    JsxToken::IDENTIFIER 
                    JsxToken::EQUALS 
                    expression 
                    JsxToken::SEMICOLON
                  { return new ConstDeclaration($2, $4); }
                  ;

expression := jsxElement  { return $1; }
            | identifier  { return $1; }
            | number      { return $1; }
            ;

jsxElement := jsxSelfClosing { return $1; }
            | jsxWithChildren { return $1; }
            ;

jsxSelfClosing := JsxToken::JSX_TAG_OPEN
                  JsxToken::IDENTIFIER
                  jsxAttributes
                  JsxToken::JSX_TAG_SELF_CLOSE
                { return new JsxElement($2, $3, []); }
                ;

jsxWithChildren := JsxToken::JSX_TAG_OPEN
                   JsxToken::IDENTIFIER
                   jsxAttributes
                   JsxToken::JSX_TAG_CLOSE
                   jsxChildren
                   JsxToken::JSX_TAG_END_OPEN
                   JsxToken::IDENTIFIER
                   JsxToken::JSX_TAG_CLOSE
                 { 
                     if ($2 !== $7) {
                         throw new \Exception("Mismatched tags: <$2> and </$7>");
                     }
                     return new JsxElement($2, $3, $5); 
                 }
                 ;

jsxAttributes := jsxAttribute jsxAttributes { return [$1, ...$2]; }
               |                            { return []; }
               ;

jsxAttribute := JsxToken::JSX_ATTR_NAME 
                JsxToken::EQUALS 
                JsxToken::STRING
              { return new JsxAttribute($1, $3); }
              | JsxToken::JSX_ATTR_NAME 
                JsxToken::EQUALS 
                JsxToken::BRACE_OPEN 
                expression 
                JsxToken::BRACE_CLOSE
              { return new JsxAttribute($1, $4); }
              ;

jsxChildren := jsxChild jsxChildren { return [$1, ...$2]; }
             |                      { return []; }
             ;

jsxChild := jsxElement                { return $1; }
          | JsxToken::JSX_TEXT        { return new TextNode($1); }
          | JsxToken::BRACE_OPEN 
            expression 
            JsxToken::BRACE_CLOSE     { return new Interpolation($2); }
          ;

identifier := JsxToken::IDENTIFIER { return new Identifier($1); }
            ;

number := JsxToken::NUMBER { return new NumberLiteral($1); }
        ;