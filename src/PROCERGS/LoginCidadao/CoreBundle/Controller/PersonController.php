<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use LoginCidadao\CoreBundle\Controller\PersonController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PersonController extends BaseController
{
    /**
     * @Template()
     */
    public function connectNfgFragmentAction()
    {
        /** @var MeuRSHelper $helper */
        $helper = $this->get('meurs.helper');
        $personMeuRS = $helper->getPersonMeuRS($this->getUser(), true);

        return compact('personMeuRS');
    }
}
