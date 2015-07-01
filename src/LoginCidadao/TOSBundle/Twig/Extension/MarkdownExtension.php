<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFilter;
use cebe\markdown\Parser;

class MarkdownExtension extends Twig_Extension
{
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('markdown', array($this, 'markdownFilter')),
        );
    }

    public function markdownFilter($text, Parser $parser = null)
    {
        $markup = $this->parser->parse($text);
        return $markup;
    }

    public function getName()
    {
        return 'markdown';
    }
}
