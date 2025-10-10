%class GrammarFileParser;

%namespace Resrap\Component\Grammar;

%use Resrap\Component\Grammar\Token;
%use Resrap\Component\Grammar\Ast\GrammarFile;
%use Resrap\Component\Grammar\Ast\GrammarDefinition;
%use Resrap\Component\Grammar\Ast\RuleDefinition;
%use Resrap\Component\Grammar\Ast\RuleToken;
%use Resrap\Component\Grammar\Ast\UseStatement;
%use Resrap\Component\Grammar\Ast\Directive;
%use Resrap\Component\Grammar\GrammarFileBuilder;

%start grammar_file;

// ============================================================
// Grammar File Structure
// ============================================================

grammar_file :=
    directives grammar_definitions
    { return new GrammarFileBuilder()->build($1, $2); }
    ;

// ============================================================
// Directives (can appear in any order)
// ============================================================

directives :=
    directive directives
    { return [$1, ...$2]; }
    | directive
    { return [$1]; }
    ;

directive :=
    Token::NAMESPACE qualified_identifier Token::SEMICOLON
    { return new Directive('namespace', $2); }
    | Token::DEFINE_CLASSNAME Token::IDENTIFIER Token::SEMICOLON
    { return new Directive('class', $2); }
    | Token::START Token::IDENTIFIER Token::SEMICOLON
    { return new Directive('start', $2); }
    | Token::USE qualified_identifier Token::SEMICOLON
    { return new UseStatement($2); }
    | Token::USE qualified_identifier Token::AS Token::IDENTIFIER Token::SEMICOLON
    { return new UseStatement($2, $4); }
    ;

// ============================================================
// Qualified Identifiers (Foo\Bar\Baz)
// ============================================================

qualified_identifier :=
    Token::IDENTIFIER
    { return $1; }
    | qualified_identifier Token::BACKSLASH Token::IDENTIFIER
    { return "{$1}\\{$3}"; }
    ;

// ============================================================
// Grammar Definitions (expr := foo bar { code };)
// ============================================================

grammar_definitions :=
    grammar_definition grammar_definitions
    { return [$1, ...$2]; }
    | grammar_definition
    { return [$1]; }
    ;

grammar_definition :=
    Token::IDENTIFIER Token::ASSIGN rule_alternatives Token::SEMICOLON
    { return new GrammarDefinition($1, $3); }
    ;

// ============================================================
// Rule Alternatives (foo | bar | baz)
// ============================================================

rule_alternatives :=
    rule_production Token::PIPE rule_alternatives
    { return [$1, ...$3]; }
    | rule_production
    { return [$1]; }
    ;

rule_production :=
    rule_tokens Token::CODE_BLOCK
    { return new RuleDefinition($1, trim($2)); }
    | Token::CODE_BLOCK
    { return new RuleDefinition([], trim($1)); }
    ;

// ============================================================
// Rule Tokens (Token::FOO, identifier, "literal", 'c')
// ============================================================

rule_tokens :=
    rule_token rule_tokens
    { return [$1, ...$2]; }
    | rule_token
    { return [$1]; }
    ;

rule_token :=
    qualified_identifier Token::STATIC_ACCESS Token::IDENTIFIER
    { return new RuleToken("{$1}::{$3}", RuleToken::TOK); }
    | Token::IDENTIFIER
    { return new RuleToken($1, RuleToken::EXPR); }
    | Token::STRING
    { return new RuleToken($1, RuleToken::LITERAL); }
    | Token::CHAR
    { return new RuleToken($1, RuleToken::LITERAL); }
    ;
