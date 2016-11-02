<?php

namespace Ehimen\Jaslang\Engine\Parser\Exception;

use Ehimen\Jaslang\Engine\Lexer\Token;

class UnexpectedTokenException extends SyntaxErrorException
{
    private $token;
    
    public function __construct($input, Token $token)
    {
        parent::__construct($input, 'Unexpected token: ' . $token->getValue() . ' @' . $token->getPosition());
        
        $this->token = $token;
    }
}