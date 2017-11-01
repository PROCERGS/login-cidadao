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
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Helper\ScopeFinderHelper;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class OAuthEventListener
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PersonRepository */
    private $personRepo;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    /** @var ScopeFinderHelper */
    private $scopeFinder;

    public function __construct(
        EntityManagerInterface $em,
        ScopeFinderHelper $scopeFinder,
        SubjectIdentifierService $subjectIdentifierService
    ) {
        $this->em = $em;
        $this->personRepo = $this->em->getRepository('LoginCidadaoCoreBundle:Person');
        $this->scopeFinder = $scopeFinder;
        $this->subjectIdentifierService = $subjectIdentifierService;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        $scope = $this->scopeFinder->getScope();
        /** @var PersonInterface $user */
        $user = $this->getUser($event);
        if (!$user) {
            return;
        }

        /** @var ClientInterface $client */
        $client = $event->getClient();

            $event->setAuthorizedClient(
            $user->isAuthorizedClient($client, $scope)
            );

        if ($event->isAuthorizedClient()) {
            $this->checkSubjectIdentifierPersisted($user, $client);
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if (!$event->isAuthorizedClient()) {
            return;
        }

        /** @var Client $client */
        $client = $event->getClient();

        /** @var PersonInterface $user */
        $user  = $this->getUser($event);
        $scope = $this->scopeFinder->getScope();

        /** @var Authorization $currentAuth */
        $currentAuth = $this->getCurrentAuthorization($user, $client);

        $authorizationEvent = new AuthorizationEvent($user, $client, $scope);

        $authEventName = LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION;
        if ($currentAuth instanceof Authorization) {
            // if the authorization is already there, update it.
            $authEventName = LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION;
            $authorizationEvent->setAuthorization($currentAuth);
            $this->checkSubjectIdentifierPersisted($user, $client);
        }

        $dispatcher->dispatch($authEventName, $authorizationEvent);

        $subjectIdentifier = $this->subjectIdentifierService->getSubjectIdentifier($user, $client->getMetadata());
        $sub = new SubjectIdentifier();
        $sub->setPerson($user)
            ->setClient($client)
            ->setSubjectIdentifier($subjectIdentifier);

        $this->em->persist($authorizationEvent->getAuthorization());
        $this->em->persist($sub);

        $this->em->flush();
    }

    public function getUser(OAuthEvent $event)
    {
        return $this->personRepo->findOneBy(['username' => $event->getUser()->getUsername()]);
    }

    private function checkSubjectIdentifierPersisted(PersonInterface $person, ClientInterface $client)
    {
        if ($this->subjectIdentifierService->isSubjectIdentifierPersisted($person, $client)) {
            return;
        }

        $subjectIdentifier = $this->subjectIdentifierService->getSubjectIdentifier($person, $client->getMetadata());
        $sub = new SubjectIdentifier();
        $sub->setPerson($person)
            ->setClient($client)
            ->setSubjectIdentifier($subjectIdentifier);
        $this->em->persist($sub);
        $this->em->flush($sub);
    }

    private function getCurrentAuthorization(PersonInterface $person, ClientInterface $client)
    {
        /** @var Authorization $currentAuth */
        $currentAuth = $this->em->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(['person' => $person, 'client' => $client,]);

        return $currentAuth;
    }
}
