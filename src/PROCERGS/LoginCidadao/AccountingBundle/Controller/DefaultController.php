<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use PROCERGS\LoginCidadao\AccountingBundle\Service\AccountingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/accounting.{_format}", name="lc_accounting_data", defaults={"_format": "json"})
     */
    public function indexAction()
    {
        $start = new \DateTime('-1 month');
        $end = new \DateTime();

        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');

        return new JsonResponse($accountingService->getAccounting($start, $end));
    }
}
