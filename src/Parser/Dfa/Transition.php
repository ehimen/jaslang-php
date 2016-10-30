<?php

namespace Ehimen\Jaslang\Parser\Dfa;

class Transition
{
    private $from;
    private $to;
    private $how;
    
    public function __construct($from, $to, $how)
    {
        $this->from = $from;
        $this->to   = $to;
        $this->how  = $how;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getHow()
    {
        return $this->how;
    }
}