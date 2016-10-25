<?php

namespace Ehimen\Jaslang;

use Ehimen\Jaslang\Evaluator\Evaluator;
use Ehimen\Jaslang\Evaluator\JaslangInvoker;
use Ehimen\Jaslang\FuncDef\Core\Danny;
use Ehimen\Jaslang\FuncDef\Core\Random;
use Ehimen\Jaslang\FuncDef\Core\Substring;
use Ehimen\Jaslang\FuncDef\Core\Subtract;
use Ehimen\Jaslang\FuncDef\Core\Sum;
use Ehimen\Jaslang\Evaluator\CallableRepository;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Lexer\DoctrineLexer;
use Ehimen\Jaslang\Operator\Core\Addition;
use Ehimen\Jaslang\Operator\Core\Identity;
use Ehimen\Jaslang\Operator\Core\Subtraction;
use Ehimen\Jaslang\Operator\Operator;
use Ehimen\Jaslang\Parser\JaslangParser;

/**
 * Initialises a Jaslang expression evaluator.
 */
class JaslangFactory
{
    
    private $functions = [];
    private $operators = [];
    
    public function registerFunction($identifier, FuncDef $function)
    {
        // TODO: validate identifier against language.
        $this->functions[$identifier] = $function;
    }

    public function registerOperator($identifier, Operator $operator)
    {
        // TODO: validate identifier against language.
        $this->operators[$identifier] = $operator;
    }
    
    public function create()
    {
        $repository = new CallableRepository();
        
        // Core functions.
        $this->functions['sum']       = new Sum();
        $this->functions['subtract']  = new Subtract();
        $this->functions['substring'] = new Substring();
        $this->functions['random']    = new Random();
        
        // Core operators
        $this->operators['+']    = new Addition();
        $this->operators['-']    = new Subtraction();
        $this->operators['===']  = new Identity();
        
        // User defined functions/operators.
        foreach ($this->operators as $identifier => $operator) {
            $repository->registerOperator($identifier, $operator);
        }
        foreach ($this->functions as $identifier => $function) {
            $repository->registerFuncDef($identifier, $function);
        }
        
        $invoker = new JaslangInvoker();
        $parser  = new JaslangParser(new DoctrineLexer(array_keys($this->operators)));
        
        return new Evaluator($parser, $repository, $invoker);
    }
}