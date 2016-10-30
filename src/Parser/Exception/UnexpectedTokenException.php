<?php

namespace Ehimen\Jaslang\Parser\Exception;

use Ehimen\Jaslang\Lexer\Token;

class UnexpectedTokenException extends SyntaxErrorException
{
    private $token;
    
    public function __construct($input, Token $token)
    {
        parent::__construct($input, 'Unexpected token: ' . $token->getValue() . ' @' . $token->getPosition());
        
        $this->token = $token;
    }
}