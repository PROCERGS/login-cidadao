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
     * @Route("/api/v1/accounting.{_format}", name="lc_accounting_data", defaults={"_format": "json"})
     */
    public function indexAction()
    {
        $start = new \DateTime('-1 month');
        $end = new \DateTime();

        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');
        $data = $accountingService->getAccounting($start, $end);

        $response = [
            'date_interval' => [
                'start' => $start->format('c'),
                'end' => $end->format('c'),
            ],
            'accounting' => $data,
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/api/v1/accounting/gcs-interface.{_format}", name="lc_accounting_gcs_interface", defaults={"_format": "json"})
     */
    public function gcsInterfaceAction()
    {
        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');
        $data = $accountingService->getAccounting(
            new \DateTime("first day of previous month"),
            new \DateTime("last day of previous month")
        );

        $summary = [];
        $summaryErrors = [];
        foreach ($data as $client) {
            $procergsInitials = $client['procergs_initials'];
            $totalUsage = $client['access_tokens'] + $client['api_usage'];
            if (count($procergsInitials) !== 1) {
                $summaryErrors[] = $totalUsage;
                continue;
            }
            foreach ($procergsInitials as $initials) {
                if (array_key_exists($initials, $summary)) {
                    $summary[$initials] += $totalUsage;
                } else {
                    $summary[$initials] = $totalUsage;
                }
            }
        }

        $today = new \DateTime();
        $month = (new \DateTime('-1 month'))->format('mY');
        $header = [
            '1',
            'LOGCIDADAO',
            $month,
            $today->format('dmY'),
        ];
        $body = [];
        foreach ($summary as $initials => $usage) {
            $body[] = implode(
                ';',
                [
                    '2',
                    '',
                    $initials,
                    $usage,
                ]
            );
        }
        foreach ($summaryErrors as $usage) {
            $body[] = implode(
                ';',
                [
                    '2',
                    '',
                    '',
                    $usage,
                ]
            );
        }
        $tail = [
            '9',
            count($body),
        ];

        echo '<pre>';
        echo implode(';', $header).PHP_EOL;
        echo implode("\n", $body).PHP_EOL;
        echo implode(';', $tail).PHP_EOL;
        echo '</pre>';
    }
}
