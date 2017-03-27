<?php

namespace Ehimen\Jaslang\Engine\Lexer;

interface Lexer
{
    const TOKEN_LITERAL_STRING = 'string';
    const TOKEN_IDENTIFIER     = 'identifier';
    const TOKEN_OPERATOR       = 'operator';
    const TOKEN_LITERAL        = 'literal';        // Any custom literals.
    const TOKEN_LEFT_PAREN     = 'left-paren';
    const TOKEN_RIGHT_PAREN    = 'right-paren';
    const TOKEN_COMMA          = 'comma';
    const TOKEN_WHITESPACE     = 'whitespace';
    const TOKEN_BACKSLASH      = 'backslash';
    const TOKEN_UNKNOWN        = 'unknown';
    const TOKEN_STATETERM      = ';';
    const TOKEN_LEFT_BRACE     = 'left-brace';
    const TOKEN_RIGHT_BRACE    = 'right-brace';
    const TOKEN_LEFT_BRACKET   = 'left-bracket';
    const TOKEN_RIGHT_BRACKET  = 'right-bracket';
    // TODO: angle bracket. Collisions with gt/lt operators?
    
    const ESCAPE_CHAR = '\\';
    
    const ESCAPABLE_CHARS = ['\\', '"', "'"];
    
    const LITERAL_TOKENS = [
        self::TOKEN_LITERAL_STRING,
        self::TOKEN_LITERAL,
    ];

    // TODO: Potential weakness in lexer here. If we add to tuple tokens,
    // TODO: we need to make sure we keep a stack of opening tokens
    // TODO: to ensure the next closing tuple token is the correct one.
    // TODO: E.g. [1, 2, 3} should be invalid.
    const TUPLE_OPEN_TOKENS = [
        self::TOKEN_LEFT_BRACKET,
    ]; 
    
    const TUPLE_CLOSE_TOKENS = [
        self::TOKEN_RIGHT_BRACKET,
    ]; 

    /**
     * @param string $input
     *
     * @return Token[]
     */
    public function tokenize($input);
}
