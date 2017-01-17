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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
        uasort(
            $data,
            function ($a, $b) {
                if ($a['access_tokens'] === $b['access_tokens']) {
                    return 0;
                }

                return ($a['access_tokens'] < $b['access_tokens']) ? 1 : -1;
            }
        );

        return [
            'data' => $data,
        ];
    }

    /**
     * @Route("/admin/accounting/{clientId}", name="lc_admin_accounting_edit_link")
     * @Template
     */
    public function editAction(Request $request, $clientId)
    {
        $client = $this->getClient($clientId);
        $link = $this->getProcergsLink($client);

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
            $link = new ProcergsLink();
            $link->setClient($client);
        }

        return $link;
    }
}
