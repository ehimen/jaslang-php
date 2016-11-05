<?php

namespace Ehimen\Jaslang\Engine\Lexer;

class Token
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $position;
    
    public function __construct($value, $type, $position)
    {
        $this->value    = $value;
        $this->type     = $type;
        $this->position = $position;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function isLiteral()
    {
        return in_array($this->getType(), Lexer::LITERAL_TOKENS, true);
    }
}
