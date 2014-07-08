<?php
namespace PROCERGS\LoginCidadao\CoreBundle\DQL;

use Doctrine\ORM\Query\Lexer, Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * ToCharFunction ::= "TO_CHAR" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class Right extends FunctionNode
{

    public $firstDateExpression = null;

    public $secondDateExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->firstDateExpression = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->secondDateExpression = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'RIGHT(' . $this->firstDateExpression->dispatch($sqlWalker) . ', ' . $this->secondDateExpression->dispatch($sqlWalker) . ')'; // (7)
    }
}