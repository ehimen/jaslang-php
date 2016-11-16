<?php

namespace Ehimen\Jaslang\Engine;

use Ehimen\Jaslang\Engine\Ast\Node\Block;
use Ehimen\Jaslang\Engine\Ast\Node\Identifier;
use Ehimen\Jaslang\Engine\Ast\Node\Operator;
use Ehimen\Jaslang\Engine\Ast\Node\Container;
use Ehimen\Jaslang\Engine\Ast\Node\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Node\Literal;
use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Ast\Node\ParentNode;
use Ehimen\Jaslang\Engine\Ast\Node\Statement;
use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Evaluator\Context\ContextFactory;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\Invoker;
use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\FuncDef\Arg;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\Parser\Parser;

class Interpreter
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Evaluator
     */
    private $evaluator;

    public function __construct(
        Parser $parser,
        Evaluator $evaluator
    ) {
        $this->parser    = $parser;
        $this->evaluator = $evaluator;
    }

    /**
     * @param $input
     *
     * @return string
     * @throws RuntimeException
     */
    public function run($input)
    {
        $ast = $this->parser->parse($input);
        
        $this->evaluator->reset();
        
        try {
            $this->evaluator->visitRoot($ast);
        } catch (RuntimeException $e) {
            $e->setEvaluationTrace($this->evaluator->getTrace());
            $e->setInput($input);
            throw $e;
        }

        return $this->evaluator->getResult()->toString();
    }
}
