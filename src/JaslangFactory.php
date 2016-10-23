<?php

namespace Ehimen\Jaslang;

use Ehimen\Jaslang\Evaluator\Evaluator;
use Ehimen\Jaslang\Evaluator\SimpleInvoker;
use Ehimen\Jaslang\FuncDef\Core\Danny;
use Ehimen\Jaslang\FuncDef\Core\Random;
use Ehimen\Jaslang\FuncDef\Core\Substring;
use Ehimen\Jaslang\FuncDef\Core\Subtract;
use Ehimen\Jaslang\FuncDef\Core\Sum;
use Ehimen\Jaslang\FuncDef\Repository;
use Ehimen\Jaslang\Parser\JaslangParser;

/**
 * Initialises a Jaslang expression evaluator.
 */
class JaslangFactory
{
    public function createDefault()
    {
        $repository = new Repository();
        $repository->registerFuncDef('sum', new Sum());
        $repository->registerFuncDef('subtract', new Subtract());
        $repository->registerFuncDef('substring', new Substring());
        $repository->registerFuncDef('random', new Random());
        
        $invoker = new SimpleInvoker();
        $parser  = JaslangParser::createDefault();
        
        return new Evaluator($parser, $repository, $invoker);
    }
}