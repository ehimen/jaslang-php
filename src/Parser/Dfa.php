<?php

namespace Ehimen\Jaslang\Parser;

class Dfa
{
    private $states = [];
    
    private $onEntering = [];
    
    private $current;
    
    public function __construct(array $rules, $start, $accept)
    {
        foreach ($rules as $rule) {
            $from  = $rule[0];
            $how   = $rule[1];
            $to    = $rule[2];
            $onEnter = isset($rule[3]) ? $rule[3] : null;
            
            if (!isset($this->states[$from])) {
                $this->states[$from] = [];
            }
            
            if (!isset($this->states[$to])) {
                $this->states[$to] = [];
            }
            
            if ($onEnter instanceof \Closure) {
                if (!isset($this->onEntering[$to][$how])) {
                    $this->onEntering[$to][$how] = [];
                }
                
                $this->onEntering[$to][$how] = $onEnter;
            }
            
            $this->states[$from][$how] = $to;
        }
        
        $this->current = $start;
    }

    public function transition($path)
    {
        if (!isset($this->states[$this->current][$path])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot transition from current state "%s" via path "%s"',
                $this->current,
                $path
            ));
        }

        $this->current = $this->states[$this->current][$path];

        if (isset($this->onEntering[$this->current][$path])) {
            $this->onEntering[$this->current][$path]();
        }
    }
}