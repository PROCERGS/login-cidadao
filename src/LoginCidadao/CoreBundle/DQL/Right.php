<?php
namespace LoginCidadao\CoreBundle\DQL;

use Doctrine\ORM\Query\Lexer, Doctrine\ORM\Query\AST\Functions\FunctionNode;

class Right extends FunctionNode
{

    public $var1 = null;

    public $var2 = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->var1 = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->var2 = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'RIGHT('.$this->var1->dispatch($sqlWalker).', '.$this->var2->dispatch($sqlWalker).')'; // (7)
    }
}