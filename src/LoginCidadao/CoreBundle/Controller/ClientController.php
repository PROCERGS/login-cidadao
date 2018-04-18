<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClientController extends Controller
{

    /**
     * @Route("/client/view/{clientId}", name="lc_app_details")
     * @Template()
     */
    public function viewAction($clientId)
    {
        $em = $this->getDoctrine()->getManager();

        $clients = $em->getRepository('LoginCidadaoOAuthBundle:Client');
        $client = $clients->find($clientId);
        $user = $this->getUser();

        /** @var Authorization|null $authorization */
        $authorization = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(['person' => $user, 'client' => $client]);

        /** @var RemoteClaimManagerInterface $remoteClaimManager */
        $remoteClaimManager = $this->get('lc.remote_claims.manager');
        $remoteClaims = $authorization ? $remoteClaimManager->getRemoteClaimsFromAuthorization($authorization) : [];

        $scopes = empty($authorization) ? [] : $authorization->getScope();

        $form = $this->createForm(
            'LoginCidadao\CoreBundle\Form\Type\RevokeAuthorizationFormType',
            ['client_id' => $clientId]
        )->createView();

        return [
            'user' => $user,
            'client' => $client,
            'scopes' => $remoteClaimManager->filterRemoteClaims($scopes),
            'remoteClaims' => $remoteClaims,
            'form' => $form,
        ];
    }
}
