<?php

namespace Ehimen\Jaslang\Lexer;

interface Lexer
{
    const TOKEN_QUOTED = 'quoted';
    const TOKEN_UNQUOTED = 'unquoted';
    const TOKEN_LEFT_PAREN = 'left-paren';
    const TOKEN_RIGHT_PAREN = 'right-paren';
    const TOKEN_QUOTE_SINGLE = 'single-quote';
    const TOKEN_QUOTE_DOUBLE = 'double-quote';
    const TOKEN_COMMA = 'comma';
    const TOKEN_WHITESPACE = 'whitespace';
    
    const ESCAPE_CHAR = '\\';
    
    const ESCAPABLE_CHARS = ['\\', '"', "'"];
    
    public function tokenize($input);
}