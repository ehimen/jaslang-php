<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\OutputBuffer;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;

/**
 * A null context to satisfy calls whilst context is being fleshed out.
 */
class NullContext implements EvaluationContext
{
    public function getSymbolTable()
    {
        return new SymbolTable();
    }

    public function getTypeRepository()
    {
        return new TypeRepository();
    }
    
    public function getVariableOfTypeOrThrow($name, Type $type)
    {
        throw new UndefinedSymbolException($name);
    }

    public function evaluateInContext(Node $node)
    {
        
    }

    public function getOutputBuffer()
    {
        return new OutputBuffer();
    }
}
