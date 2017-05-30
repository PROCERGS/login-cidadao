<?php

namespace LoginCidadao\OAuthBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OAuthEventListener
{
    /** @var PersonRepository */
    private $personRepo;

    /** @var Request */
    private $request;

    /** @var Form */
    private $form;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    public function __construct(
        EntityManager $em,
        RequestStack $requestStack,
        Form $form,
        SubjectIdentifierService $subjectIdentifierService
    ) {
        $this->em = $em;
        $this->personRepo = $this->em->getRepository('LoginCidadaoCoreBundle:Person');
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->subjectIdentifierService = $subjectIdentifierService;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        $scope = $this->getScope();
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

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if (!$event->isAuthorizedClient()) {
            return;
        }

        /** @var Client $client */
        $client = $event->getClient();
        if (null === $client) {
            return;
        }

        /** @var PersonInterface $user */
        $user = $this->getUser($event);
        $scope = $this->getScope();

        $authRepo = $this->em->getRepository('LoginCidadaoCoreBundle:Authorization');
        $currentAuth = $authRepo->findOneBy(
            [
                'person' => $user,
                'client' => $client,
            ]
        );

        // if the authorization is already there, update it.
        if ($currentAuth instanceof Authorization) {
            $merged = array_merge($currentAuth->getScope(), $scope);
            $currentAuth->setScope($merged);
            $this->checkSubjectIdentifierPersisted($user, $client);
        } else {
            $authorization = new Authorization();
            $authorization->setClient($client);
            $authorization->setPerson($user);
            $authorization->setScope($scope);

            $subjectIdentifier = $this->subjectIdentifierService->getSubjectIdentifier($user, $client->getMetadata());
            $sub = new SubjectIdentifier();
            $sub->setPerson($user)
                ->setClient($client)
                ->setSubjectIdentifier($subjectIdentifier);

            $this->em->persist($authorization);
            $this->em->persist($sub);
        }

        $this->em->flush();
    }

    public function getUser(OAuthEvent $event)
    {
        return $this->personRepo
            ->findOneBy(
                array(
                    'username' => $event->getUser()->getUsername(),
                )
            );
    }

    protected function getScope()
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
}
