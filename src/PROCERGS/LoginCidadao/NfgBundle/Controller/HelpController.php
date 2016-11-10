<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use Ejsmont\CircuitBreaker\CircuitBreakerInterface;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HelpController
 * @package PROCERGS\LoginCidadao\NfgBundle\Controller
 *
 * @Route("/help")
 */
class HelpController extends Controller
{
    /**
     * @Route("/connection-not-found", name="nfg_help_connection_not_found")
     */
    public function connectionNotFoundAction()
    {
        return $this->render('PROCERGSNfgBundle:Help:connectionNotFound.html.twig');
    }
}
