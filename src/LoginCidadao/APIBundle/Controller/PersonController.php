<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Controller;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use LoginCidadao\CoreBundle\LongPolling\LongPollingUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientUser;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;
use LoginCidadao\APIBundle\Entity\LogoutKey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PersonController extends BaseController
{

    /**
     * Gets the currently authenticated user.
     *
     * The returned object contents will depend on the scope the user authorized.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the currently authenticated user.",
     *   output = {
     *     "class"="LoginCidadao\CoreBundle\Entity\Person",
     *     "groups" = {"public_profile"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="person")
     * @REST\Get(defaults={"version": 2})
     * @Audit\Loggable(type="SELECT")
     * @throws NotFoundHttpException
     */
    public function getPersonAction()
    {
        $person = $this->getUser();
        if ($person instanceof PersonInterface) {
            $scope = $this->getClientScope($person);
        } else {
            if ($person instanceof ClientUser) {
                throw new AccessDeniedException("This is only available to a person's Access Token, not a client's.");
            } else {
                throw new AccessDeniedException();
            }
        }

        return $this->renderWithContext($person, $this->getSerializationContext($scope));
    }

    /**
     * Waits for a change in the current user's profile.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Waits for a change in the current user's profile.",
     *   output = {
     *     "class"="LoginCidadao\CoreBundle\Entity\Person",
     *     "groups" = {"public_profile"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     408 = "Returned when the request times out"
     *   }
     * )
     * @REST\Get("/wait/person/update")
     * @Audit\Loggable(type="SELECT")
     * @REST\View
     */
    public function waitPersonChangeAction(Request $request)
    {
        /** @var LongPollingUtils $longPolling */
        $longPolling = $this->get('long_polling');

        $user = $this->getUser();
        $updatedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $request->get('updated_at'));

        if (!$updatedAt instanceof \DateTime) {
            $updatedAt = new \DateTime();
        }

        $callback = $longPolling->getEntityUpdateCheckerCallback($user, $updatedAt);
        $person = $longPolling->runTimeLimited($callback);

        return $this->renderWithContext($person, $this->getSerializationContext($this->getClientScope($user)));
    }

    /**
     * Generates and returns a logout key for the user.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Generates and returns a logout key for the user.",
     *   output = {
     *     "class"="LoginCidadao\APIBundle\Entity\LogoutKey",
     *     "groups" = {"key"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\Route("/person/{id}/logout-key", methods={"PUT", "POST"})
     * @REST\View(templateVar="logoutKey")
     *
     * @throws NotFoundHttpException
     */
    public function getLogoutKeyAction($id)
    {
        /** @var OAuthToken $token */
        $token = $this->get('security.token_storage')->getToken();
        $accessToken = $this->getDoctrine()
            ->getRepository('LoginCidadaoOAuthBundle:AccessToken')
            ->findOneBy(['token' => $token->getToken()]);
        $client = $accessToken->getClient();

        $people = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');
        $person = $people->find($id);

        if (!$person->hasAuthorization($client)) {
            throw new AccessDeniedException("Not authorized");
        }

        $logoutKey = new LogoutKey();
        $logoutKey->setPerson($person);
        $logoutKey->setClient($client);
        $logoutKey->setKey($logoutKey->generateKey());

        $em = $this->getDoctrine()->getManager();
        $em->persist($logoutKey);
        $em->flush();

        return [
            'key' => $logoutKey->getKey(),
            'url' => $this->generateUrl(
                'lc_logout_not_remembered_safe',
                ['key' => $logoutKey->getKey()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];
    }
}
