<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Twig\Extension;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;

class RsExtension extends \Twig_Extension
{
    /** @var MeuRSHelper */
    private $rsHelper;

    /**
     * RsExtension constructor.
     * @param MeuRSHelper $rsHelper
     */
    public function __construct(MeuRSHelper $rsHelper)
    {
        $this->rsHelper = $rsHelper;
    }

    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('personRS', [$this, 'getPersonRS']),
        ];
    }

    public function getPersonRS(PersonInterface $person)
    {
        return $this->rsHelper->getPersonMeuRS($person);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'person_rs_twig_extension';
    }
}
