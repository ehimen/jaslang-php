<?php

namespace Ehimen\Jaslang\Engine\Lexer;

interface Lexer
{
    const TOKEN_LITERAL_STRING = 'string';
    const TOKEN_IDENTIFIER = 'identifier';
    const TOKEN_OPERATOR = 'operator';
    const TOKEN_LITERAL = 'literal';        // Any custom literals.
    const TOKEN_LEFT_PAREN = 'left-paren';
    const TOKEN_RIGHT_PAREN = 'right-paren';
    const TOKEN_COMMA = 'comma';
    const TOKEN_WHITESPACE = 'whitespace';
    const TOKEN_BACKSLASH = 'backslash';
    const TOKEN_UNKNOWN = 'unknown';
    const TOKEN_STATETERM = ';';
    
    const ESCAPE_CHAR = '\\';
    
    const ESCAPABLE_CHARS = ['\\', '"', "'"];
    
    const LITERAL_TOKENS = [
        self::TOKEN_LITERAL_STRING,
        self::TOKEN_LITERAL,
    ];

    /**
     * @param string $input
     *
     * @return Token[]
     */
    public function tokenize($input);
}
