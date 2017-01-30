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
use PROCERGS\LoginCidadao\AccountingBundle\Service\AccountingService;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin/accounting", name="lc_admin_accounting_summary")
     * @Template
     * @Security("has_role('ROLE_ACCOUNTING_VIEW')")
     */
    public function indexAction()
    {
        /** @var AccountingService $accountingService */
        $accountingService = $this->get('procergs.lc.accounting');

        $start = new \DateTime('-7 days');
        $end = new \DateTime();
        $data = $accountingService->getAccounting($start, $end);

        // Reverse sort by access_token
        uasort(
            $data,
            function ($a, $b) {
                $totalA = $a['access_tokens'] + $a['api_usage'];
                $totalB = $b['access_tokens'] + $b['api_usage'];
                if ($totalA === $totalB) {
                    return 0;
                }

                return ($totalA < $totalB) ? 1 : -1;
            }
        );

        return [
            'data' => $data,
        ];
    }

    /**
     * @Route("/admin/accounting/{clientId}", name="lc_admin_accounting_edit_link")
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
