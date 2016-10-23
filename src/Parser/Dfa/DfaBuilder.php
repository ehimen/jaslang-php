<?php

namespace Ehimen\Jaslang\Parser\Dfa;

class DfaBuilder
{
    private $rules = [];
    private $start;
    private $accepted = [];

    public function addRule($from, $path, $to, $what = null)
    {
        if (!is_array($path)) {
            $path = [$path];
        }
        
        foreach ($path as $p) {
            $this->rules[] = [$from, $p, $to, $what];
        }
        
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
        $dfa = new Dfa($this->rules, $this->start, $this->accepted);
        
        $this->rules = [];
        $this->start = null;
        $this->accepted = [];
        
        return $dfa;
    }
}