<?php

declare(strict_types=1);

namespace Resrap\Examples\Jsx;

use Resrap\Component\Scanner\{
    StatefulScannerBuilder,
    Pattern,
    StateTransition,
    ScannerToken,
    Scanner
};

class JsxScanner
{
    public static function build(): Scanner
    {
        return (new StatefulScannerBuilder())
            // State: Normal JavaScript
            ->state('js', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('const', JsxToken::CONST),
                new Pattern('let', JsxToken::LET),
                new Pattern('var', JsxToken::VAR),
                new Pattern('function', JsxToken::FUNCTION),
                new Pattern('<(?=[a-zA-Z])', JsxToken::JSX_TAG_OPEN),
                new Pattern('[a-zA-Z_][a-zA-Z0-9_]*', JsxToken::IDENTIFIER),
                new Pattern('[0-9]+', JsxToken::NUMBER),
                new Pattern('"(?:[^"\\\\]|\\\\.)*"', JsxToken::STRING),
                new Pattern("'(?:[^'\\\\]|\\\\.)*'", JsxToken::STRING),
                new Pattern('=', JsxToken::EQUALS),
                new Pattern('\+', JsxToken::PLUS),
                new Pattern('-', JsxToken::MINUS),
                new Pattern('\*', JsxToken::STAR),
                new Pattern('\/', JsxToken::SLASH),
                new Pattern('\{', JsxToken::BRACE_OPEN),
                new Pattern('\}', JsxToken::BRACE_CLOSE),
                new Pattern('\(', JsxToken::PAREN_OPEN),
                new Pattern('\)', JsxToken::PAREN_CLOSE),
                new Pattern(';', JsxToken::SEMICOLON),
                new Pattern(',', JsxToken::COMMA),
            ], [
                new StateTransition([JsxToken::JSX_TAG_OPEN], 'jsx-tag-name'),
            ])

            // State: JSX tag name
            ->state('jsx-tag-name', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('[a-zA-Z][a-zA-Z0-9]*', JsxToken::IDENTIFIER),
            ], [
                new StateTransition([JsxToken::IDENTIFIER], 'jsx-tag-attrs'),
            ])

            // State: JSX tag attributes
            ->state('jsx-tag-attrs', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('[a-z][a-zA-Z0-9]*', JsxToken::JSX_ATTR_NAME),
                new Pattern('=', JsxToken::EQUALS),
                new Pattern('"[^"]*"', function (string &$value) {
                    $value = substr($value, 1, -1);
                    return JsxToken::STRING;
                }),
                new Pattern('\{', JsxToken::BRACE_OPEN),
                new Pattern('>', JsxToken::JSX_TAG_CLOSE),
                new Pattern('\/>', JsxToken::JSX_TAG_SELF_CLOSE),
                new Pattern('<\/', JsxToken::JSX_TAG_END_OPEN),
            ], [
                new StateTransition([JsxToken::JSX_TAG_CLOSE], 'jsx-content'),
                new StateTransition([JsxToken::JSX_TAG_SELF_CLOSE], 'jsx-after'),
                new StateTransition([JsxToken::JSX_TAG_END_OPEN], 'jsx-closing'),
                new StateTransition([JsxToken::BRACE_OPEN], 'jsx-interpolation-attr'),
            ])

            // State: JSX content
            ->state('jsx-content', [
                new Pattern('[^<{]+', JsxToken::JSX_TEXT),
                new Pattern('<(?=[a-zA-Z])', JsxToken::JSX_TAG_OPEN),
                new Pattern('<\/', JsxToken::JSX_TAG_END_OPEN),
                new Pattern('\{', JsxToken::BRACE_OPEN),
            ], [
                new StateTransition([JsxToken::JSX_TAG_OPEN], 'jsx-tag-name'),
                new StateTransition([JsxToken::JSX_TAG_END_OPEN], 'jsx-closing'),
                // MUDANÇA: Vai para interpolação de CONTEÚDO
                new StateTransition([JsxToken::BRACE_OPEN], 'jsx-interpolation-content'),
            ])

            // State: Closing tag JSX
            ->state('jsx-closing', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('[a-zA-Z][a-zA-Z0-9]*', JsxToken::IDENTIFIER),
                new Pattern('>', JsxToken::JSX_TAG_CLOSE),
            ], [
                new StateTransition([JsxToken::JSX_TAG_CLOSE], 'jsx-after'),
            ])

            // State: After closing tag
            ->state('jsx-after', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern(';', JsxToken::SEMICOLON),
                new Pattern('<(?=[a-zA-Z])', JsxToken::JSX_TAG_OPEN),
                new Pattern('<\/', JsxToken::JSX_TAG_END_OPEN),
                new Pattern('[^<{;]+', JsxToken::JSX_TEXT),
                new Pattern('\{', JsxToken::BRACE_OPEN),
            ], [
                new StateTransition([JsxToken::SEMICOLON], 'js'),
                new StateTransition([JsxToken::JSX_TAG_OPEN], 'jsx-tag-name'),
                new StateTransition([JsxToken::JSX_TAG_END_OPEN], 'jsx-closing'),
                new StateTransition([JsxToken::BRACE_OPEN], 'jsx-interpolation-content'),
                new StateTransition([JsxToken::JSX_TEXT], 'jsx-content'),
            ])

            // State: Interpolation in attribute
            ->state('jsx-interpolation-attr', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('[a-zA-Z_][a-zA-Z0-9_]*', JsxToken::IDENTIFIER),
                new Pattern('[0-9]+', JsxToken::NUMBER),
                new Pattern('\+', JsxToken::PLUS),
                new Pattern('-', JsxToken::MINUS),
                new Pattern('\*', JsxToken::STAR),
                new Pattern('\/', JsxToken::SLASH),
                new Pattern('\(', JsxToken::PAREN_OPEN),
                new Pattern('\)', JsxToken::PAREN_CLOSE),
                new Pattern('\}', JsxToken::BRACE_CLOSE),
            ], [
                // Back to attributes
                new StateTransition([JsxToken::BRACE_CLOSE], 'jsx-tag-attrs'),
            ])

            // State: Interpolation in content
            ->state('jsx-interpolation-content', [
                new Pattern('[\s\t\n\r]+', ScannerToken::SKIP),
                new Pattern('[a-zA-Z_][a-zA-Z0-9_]*', JsxToken::IDENTIFIER),
                new Pattern('[0-9]+', JsxToken::NUMBER),
                new Pattern('\+', JsxToken::PLUS),
                new Pattern('-', JsxToken::MINUS),
                new Pattern('\*', JsxToken::STAR),
                new Pattern('\/', JsxToken::SLASH),
                new Pattern('\(', JsxToken::PAREN_OPEN),
                new Pattern('\)', JsxToken::PAREN_CLOSE),
                new Pattern('\}', JsxToken::BRACE_CLOSE),
            ], [
                // Back to content
                new StateTransition([JsxToken::BRACE_CLOSE], 'jsx-content'),
            ])
            ->setInitialState('js')
            ->build();
    }
}
