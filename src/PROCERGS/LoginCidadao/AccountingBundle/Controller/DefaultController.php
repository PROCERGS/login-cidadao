<?php

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
