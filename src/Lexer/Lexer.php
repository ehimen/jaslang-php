<?php

namespace Ehimen\Jaslang\Lexer;

interface Lexer
{
    const TOKEN_STRING = 'string';
    const TOKEN_IDENTIFIER = 'identifier';
    const TOKEN_UNQUOTED = 'unquoted';
    const TOKEN_LEFT_PAREN = 'left-paren';
    const TOKEN_RIGHT_PAREN = 'right-paren';
    const TOKEN_COMMA = 'comma';
    const TOKEN_WHITESPACE = 'whitespace';
    const TOKEN_BACKSLASH = 'backslash';
    
    const ESCAPE_CHAR = '\\';
    
    const ESCAPABLE_CHARS = ['\\', '"', "'"];
    
    public function tokenize($input);
}