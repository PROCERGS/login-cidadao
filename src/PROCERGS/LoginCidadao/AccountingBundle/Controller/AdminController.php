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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AdminController extends Controller
{
    /**
     * @Route("/admin/accounting", name="lc_admin_accounting_summary")
     * @Template
     */
    public function indexAction()
    {
        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');

        $start = new \DateTime('-7 days');
        $end = new \DateTime();
        $data = $accountingService->getAccounting($start, $end);

        // Reverse sort by access_token
        uasort($data, function($a, $b) {
            if ($a['access_tokens'] === $b['access_tokens']) {
                return 0;
            }

            return ($a['access_tokens'] < $b['access_tokens']) ? 1 : -1;
        });

        return [
            'data' => $data,
        ];
    }
}
