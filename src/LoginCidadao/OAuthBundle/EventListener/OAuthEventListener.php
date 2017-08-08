<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OAuthEventListener
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var FormInterface */
    private $form;

    /** @var Request */
    private $request;

    public function __construct(EntityManagerInterface $em, FormInterface $form, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->form = $form;

        $this->request = $requestStack->getCurrentRequest();
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        $user = $event->getUser();
        $client = $event->getClient();
        if (!$user instanceof PersonInterface || !$client instanceof ClientInterface) {
            return;
        }

        $event->setAuthorizedClient(
            $user->isAuthorizedClient($client, $this->getScope())
        );
    }

    public function onPostAuthorizationProcess(OAuthEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if (!$event->isAuthorizedClient()) {
            return;
        }
        $client = $event->getClient();
        if (null === $client || !$client instanceof ClientInterface) {
            return;
        }

        /** @var PersonInterface $user */
        $user = $event->getUser();
        $scope = $this->getScope();

        /** @var Authorization $currentAuth */
        $currentAuth = $this->getCurrentAuthorization($user, $client);

        $authorizationEvent = new AuthorizationEvent($user, $client, $scope);

        $authEventName = LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION;
        if ($currentAuth instanceof Authorization) {
            // if the authorization is already there, update it.
            $authEventName = LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION;
            $authorizationEvent->setAuthorization($currentAuth);
        }

        $dispatcher->dispatch($authEventName, $authorizationEvent);

        $this->em->persist($authorizationEvent->getAuthorization());
        $this->em->flush();
    }

    private function getScope()
    {
        $form = $this->form->getName();

        $scope = $this->request->request->get('scope', false);
        if (!$scope) {
            $scope = $this->request->query->get('scope', false);
        }
        if (!$scope) {
            $scope = $this->request->request->get("{$form}[scope]", false, true);
        }

        return !is_array($scope) ? explode(' ', $scope) : $scope;
    }

    private function getCurrentAuthorization(PersonInterface $person, ClientInterface $client)
    {
        /** @var Authorization $currentAuth */
        $currentAuth = $this->em->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(['person' => $person, 'client' => $client,]);

        return $currentAuth;
    }
}
