<?php
namespace LoginCidadao\CoreBundle\DQL;

use Doctrine\ORM\Query\Lexer, Doctrine\ORM\Query\AST\Functions\FunctionNode;

class CountIf extends FunctionNode
{

    public $var1 = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->var1 = $parser->ConditionalExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'count(case when '.$this->var1->dispatch($sqlWalker).' then 1 else null end)'; // (7)
    }
}