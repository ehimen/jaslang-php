<?php

namespace Ehimen\Jaslang\Engine\Parser\Dfa;

use Ehimen\Jaslang\Engine\Parser\Dfa\Exception\NotAcceptedException;
use Ehimen\Jaslang\Engine\Parser\Dfa\Exception\TransitionImpossibleException;

class Dfa
{
    private $states = [];
    
    private $whenEntering = [];
    
    private $current;
    
    private $accepted = [];

    /**
     * @param Transition[] $rules
     * @param string $start
     * @param array $accepted
     * @param array $whenEntering
     */
    public function __construct(array $rules, $start, array $accepted, array $whenEntering)
    {
        foreach ($rules as $rule) {
            $from  = $rule->getFrom();
            $how   = $rule->getHow();
            $to    = $rule->getTo();
            
            if (!isset($this->states[$from])) {
                $this->states[$from] = [];
            }
            
            if (!isset($this->states[$to])) {
                $this->states[$to] = [];
            }
            
            $this->states[$from][$how] = $to;
        }
        
        $this->whenEntering = $whenEntering;
        $this->current      = $start;
        $this->accepted     = $accepted;
    }

    public function transition($path)
    {
        if (!isset($this->states[$this->current][$path])) {
            throw new TransitionImpossibleException(sprintf(
                'Cannot transition from current state "%s" via path "%s"',
                $this->current,
                $path
            ));
        }

        $this->current = $this->states[$this->current][$path];

        if (isset($this->whenEntering[$this->current])) {
            $this->whenEntering[$this->current]();
        }
    }

    public function accept()
    {
        if (!in_array($this->current, $this->accepted, true)) {
            throw new NotAcceptedException();
        }
    }
}