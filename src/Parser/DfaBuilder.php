<?php

namespace Ehimen\Jaslang\Parser;

class DfaBuilder
{
    private $rules = [];
    private $start;
    private $accept;

    public function addRule($from, $path, $to)
    {
        if (!is_array($path)) {
            $path = [$path];
        }
        
        foreach ($path as $p) {
            $this->rules[] = [$from, $p, $to];
        }
        
        return $this;
    }

    public function whenEntering($where, $via, $do)
    {
        if (!is_array($via)) {
            $via = [$via];
        }
        
        if (!is_array($where)) {
            $where = [$where];
        }
        
        foreach ($this->rules as &$rule) {
            foreach ($via as $path) {
                foreach ($where as $destination) {
                    if (($destination === $rule[2]) && ($path === $rule[1])) {
                        if (isset($rule[3])) {
                            throw new \InvalidArgumentException(sprintf(
                                'Cannot register callback when arriving at "%s" from "%s", already registered!',
                                $destination,
                                $path
                            ));
                        }

                        $rule[3] = $do;
                    }
                }
            }
        }
        
        return $this;
    }

    public function whenLeaving($where, $from, $do)
    {
        if (!is_array($from)) {
            $from = [$from];
        }
        
        foreach ($this->rules as &$rule) {
            if (($where === $rule[2]) && ($from === $rule[1])) {
                $rule[4][] = $do;
            }
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
        $this->accept = $state;

        return $this;
    }

    public function build()
    {
        $dfa = new Dfa($this->rules, $this->start, $this->accept);
        
        $this->rules = [];
        $this->start = null;
        $this->accept = null;
        
        return $dfa;
    }
}