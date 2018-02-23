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

use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializerInterface;
use LoginCidadao\APIBundle\Controller\BaseController;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RemoteClaimController extends BaseController
{
    /**
     * @REST\Get("/api/v{version}/remote-claims/translate",
     *     name="remote_claims_validate",
     *     defaults={"_format"="json"},
     *     requirements={"version": "\d+(.\d+)*"})
     * @REST\View(templateVar="oidc_config")
     */
    public function validateRemoteClaimAction(Request $request)
    {
        $format = $request->get('_format');
        if ($format != 'json') {
            throw new \RuntimeException("Unsupported format '{$format}'");
        }

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
        $personSerializationContext = $this->getSerializationContext($authorization->getScope());
        $serializedPerson = $serializer->serialize($person, $format, $personSerializationContext);
        $serializedClient = $serializer->serialize($client, $format, $this->getSerializationContext(['remote_claim']));

        $response = [
            'claim_name' => (string)$remoteClaimAuthorization->getClaimName(),
            'userinfo' => json_decode($serializedPerson, true),
            'relying_party' => json_decode($serializedClient, true),
        ];

        $view = $this->view($response);

        return $this->handleView($view);
    }
}
