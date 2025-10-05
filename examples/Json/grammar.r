%class JsonParser;

%use Resrap\Component\Parser\GrammarRule;
%use Resrap\Examples\Json\Ast\JsonArray;
%use Resrap\Examples\Json\Ast\JsonBoolean;
%use Resrap\Examples\Json\Ast\JsonNull;
%use Resrap\Examples\Json\Ast\JsonNumber;
%use Resrap\Examples\Json\Ast\JsonObject;
%use Resrap\Examples\Json\Ast\JsonPair;
%use Resrap\Examples\Json\Ast\JsonString;
%use Resrap\Examples\Json\Token;

%start json_value;

json_value := json_object       { return $1; }
            | json_array        { return $1; }
            | Token::STRING     { return new JsonString($1); }
            | Token::NUMBER     { return new JsonNumber($1); }
            | Token::TRUE       { return new JsonBoolean($1); }
            | Token::FALSE      { return new JsonBoolean($1); }
            | Token::NULL       { return new JsonNull(); }
            ;

json_object := Token::LBRACE Token::RBRACE                          { return new JsonObject([]); }
             | Token::LBRACE json_object_members Token::RBRACE      { return new JsonObject($2); }
             ;

json_object_members := json_object_pair                                         { return [$1]; }
                     | json_object_pair Token::COMMA json_object_members        { return [$1, ...$3]; }
                     ;

json_object_pair := Token::STRING Token::COLON json_value   { return new JsonPair($1, $3); }
                  ;

json_array := Token::LBRACKET Token::RBRACKET                       { return new JsonArray([]); }
            | Token::LBRACKET json_array_elements Token::RBRACKET   { return new JsonArray($2); }
            ;

json_array_elements := json_value                                   { return [$1]; }
                     | json_value Token::COMMA json_array_elements  { return [$1, ...$3]; }
                     ;
