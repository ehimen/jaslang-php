<?php

namespace Ehimen\Jaslang\Engine\Parser\Dfa;

class DfaBuilder
{
    private $rules = [];
    private $start;
    private $accepted = [];
    private $whenEntering = [];

    public function addRule($from, $path, $to)
    {
        if (!is_array($path)) {
            $path = [$path];
        }
        
        foreach ($path as $p) {
            $this->rules[] = new Transition($from, $to, $p);
        }
        
        return $this;
    }

    public function whenEntering($where, \Closure $what)
    {
        $this->whenEntering[$where] = $what;
        
        return $this;
    }

    public function start($state)
    {
        $this->start = $state;

        return $this;
    }

    public function accept($state)
    {
        $this->accepted[] = $state;

        return $this;
    }

    public function build()
    {
        $dfa = new Dfa($this->rules, $this->start, $this->accepted, $this->whenEntering);
        
        $this->rules = [];
        $this->start = null;
        $this->accepted = [];
        
        return $dfa;
    }
}