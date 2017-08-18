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

use LoginCidadao\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Form\MonthSelectorType;
use PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReport;
use PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReportEntry;
use PROCERGS\LoginCidadao\AccountingBundle\Service\AccountingService;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @codeCoverageIgnore
 */
class AdminController extends Controller
{
    /**
     * @Route("/accounting/{month}", name="lc_admin_accounting_summary",
     *     requirements={"month" = "\d{4}-(?:0[1-9]|1[012])"}, defaults={"month" = null}
     * )
     * @Template
     * @Security("has_role('ROLE_ACCOUNTING_VIEW')")
     */
    public function indexAction($month = null)
    {
        $months = [
            (new \DateTime('first day of this month'))->modify('-3 months'),
            (new \DateTime('first day of this month'))->modify('-2 months'),
            (new \DateTime('first day of this month'))->modify('-1 month'),
            new \DateTime('first day of this month'),
        ];
        $monthChoices = [];
        foreach ($months as $choice) {
            $monthChoices[] = [
                'label' => $choice->format('m/Y'),
                'month' => $choice->format('Y-m'),
            ];
        }

        if ($month === null) {
            $month = 'previous month';
        }

        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');

        $start = new \DateTime("first day of {$month}");
        $end = new \DateTime("last day of {$month}");
        $data = $accountingService->getAccounting($start, $end)->getReport([
            'include_inactive' => false,
            'sort' => AccountingReport::SORT_ORDER_DESC,
        ]);

        return [
            'monthChoices' => $monthChoices,
            'start' => $start,
            'end' => $end,
            'data' => $data,
        ];
    }

    /**
     * @Route("/accounting/{clientId}", name="lc_admin_accounting_edit_link")
     * @Template
     * @Security("has_role('ROLE_ACCOUNTING_EDIT')")
     */
    public function editAction(Request $request, $clientId)
    {
        /** @var SystemsRegistryService $systemsRegistry */
        $systemsRegistry = $this->get('procergs.lc.procergs_systems.api');
        $client = $this->getClient($clientId);
        $link = $this->getProcergsLink($client);
        $initials = $systemsRegistry->getSystemInitials($client);
        $owners = $systemsRegistry->getSystemOwners($client);

        $form = $this->createForm('PROCERGS\LoginCidadao\AccountingBundle\Form\ProcergsLinkType', $link);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($link);
            $em->flush();

            return $this->redirectToRoute('lc_admin_accounting_summary');
        }

        return [
            'client' => $client,
            'link' => $link,
            'initials' => $initials,
            'owners' => $owners,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Client|string $publicId
     * @return Client|null
     */
    private function getClient($publicId)
    {
        if ($publicId instanceof Client) {
            return $publicId;
        }

        $id = explode('_', $publicId, 2);

        $clientRepo = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client');
        /** @var Client $client */
        $client = $clientRepo->findOneBy(['id' => $id[0], 'randomId' => $id[1]]);

        return ($client instanceof Client) ? $client : null;
    }

    /**
     * @param Client|string $client
     * @return ProcergsLink
     */
    private function getProcergsLink($client)
    {
        $client = $this->getClient($client);
        if (!$client) {
            return null;
        }
        $repo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoAccountingBundle:ProcergsLink');

        /** @var ProcergsLink $link */
        $link = $repo->findOneBy(['client' => $client]);

        if (!($link instanceof ProcergsLink)) {
            /** @var SystemsRegistryService $systemsRegistry */
            $systemsRegistry = $this->get('procergs.lc.procergs_systems.api');

            $link = new ProcergsLink();
            $link->setClient($client);
            // we assume it's an internal system (see AccountingService::addReportEntry())
            $link->setSystemType(ProcergsLink::TYPE_INTERNAL);
        }

        return $link;
    }
}
