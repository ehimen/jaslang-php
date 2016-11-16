<?php

namespace Ehimen\Jaslang\Engine\Parser\Validator;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Ast\Node\Root;
use Ehimen\Jaslang\Engine\Ast\Node\Statement;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedTokenException;
use Ehimen\Jaslang\Engine\Parser\NodeCreationObserver;

class JaslangValidator implements Validator, NodeCreationObserver
{
    /**
     * @var \SplObjectStorage
     */
    private $tokens;

    public function __construct()
    {
        $this->tokens = new \SplObjectStorage();
    }
    
    /**
     * @inheritdoc
     */
    public function validate($input, Root $ast)
    {
        foreach ($ast->getChildren() as $i => $statement) {
            if (!($statement instanceof Statement)) {
                throw new RuntimeException(sprintf(
                    'Expected children of root to be statements, but child %d was not, got: %s',
                    $i,
                    $statement->debug()
                ));
            }
            
            $isFirst = true;
            foreach ($statement->getChildren() as $child) {
                if (!$isFirst) {
                    throw new UnexpectedTokenException($input, $this->getTokenFromNode($child));
                }
                
                $isFirst = false;
            }
        }
    }

    public function onNodeCreated(Node $node, Token $currentToken)
    {
        $this->tokens[$node] = $currentToken;
    }

    private function getTokenFromNode(Node $node)
    {
        if (!isset($this->tokens[$node])) {
            throw new OutOfBoundsException(sprintf('Do not have token for node "%s"', $node->debug()));
        }
        
        return $this->tokens[$node];
    }
}
