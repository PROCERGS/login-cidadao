<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Controller;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use LoginCidadao\APIBundle\Controller\BaseController;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\CoreBundle\LongPolling\LongPollingUtils;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientUser;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;
use LoginCidadao\APIBundle\Entity\LogoutKey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoteClaimController extends BaseController
{

    /**
     * @REST\Get("/api/v1/validate-claim", name="remote_claims_validate", defaults={"_format"="json"})
     * @REST\View(templateVar="oidc_config")
     */
    public function validateRemoteClaimAction(Request $request)
    {
        /** @var ClaimProviderInterface|ClientInterface $provider */
        $provider = $this->getClient();

        $accessToken = $request->get('claim_access_token');

        /** @var RemoteClaimManagerInterface $manager */
        $manager = $this->get('lc.remote_claims.manager');

        $remoteClaimAuthorization = $manager->getRemoteClaimAuthorizationByAccessToken($provider, $accessToken);
        if (!$remoteClaimAuthorization instanceof RemoteClaimAuthorizationInterface) {
            throw $this->createNotFoundException("Authorization not found");
        }
        $person = $remoteClaimAuthorization->getPerson();
        $client = $remoteClaimAuthorization->getClient();

        /** @var AuthorizationRepository $authorizationRepo */
        $authorizationRepo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Authorization');

        /** @var Authorization $authorization */
        $authorization = $authorizationRepo->findOneBy([
            'client' => $provider,
            'person' => $person,
        ]);

        /** @var SerializerInterface $serializer */
        $serializer = $this->get('jms_serializer');
        $serializedPerson = $serializer->serialize($person, $this->getSerializationContext($authorization->getScope()));
        $serializedClient = $serializer->serialize($client, $this->getSerializationContext(['remote_claim']));

        $response = [
            'claim_name' => (string)$remoteClaimAuthorization->getClaimName(),
            'userinfo' => $serializedPerson,
            'relying_party' => $serializedClient,
        ];

        $view = $this->view($response);

        return $this->handleView($view);
    }
}
