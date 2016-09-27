<?php

namespace LoginCidadao\OAuthBundle\EventListener;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use LoginCidadao\CoreBundle\Entity\Authorization;
use Symfony\Component\HttpFoundation\Request;

class OAuthEventListener
{
    private $doctrine;
    private $personRepo;
    /** @var Request */
    private $request;
    private $form;

    public function __construct(Doctrine $doctrine, $container)
    {
        $this->doctrine   = $doctrine;
        $this->personRepo = $this->doctrine->getRepository('LoginCidadaoCoreBundle:Person');
        $this->request    = $container->get('request');
        $this->form       = $container->get('fos_oauth_server.authorize.form');
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        $scope = $this->getScope();
        $user  = $this->getUser($event);
        if ($user) {
            $event->setAuthorizedClient(
                $user->isAuthorizedClient($event->getClient(), $scope)
            );
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if (!$event->isAuthorizedClient()) {
            return;
        }
        if (null === $client = $event->getClient()) {
            return;
        }

        $user  = $this->getUser($event);
        $scope = $this->getScope();

        $em          = $this->doctrine->getManager();
        $authRepo    = $em->getRepository('LoginCidadaoCoreBundle:Authorization');
        $currentAuth = $authRepo->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));

        // if the authorization is already there, update it.
        if ($currentAuth instanceof Authorization) {
            $merged = array_merge($currentAuth->getScope(), $scope);
            $currentAuth->setScope($merged);
        } else {
            $authorization = new Authorization();
            $authorization->setClient($client);
            $authorization->setPerson($user);
            $authorization->setScope($scope);
            $em->persist($authorization);
        }

        $em->flush();
    }

    public function getUser(OAuthEvent $event)
    {
        return $this->personRepo
                ->findOneBy(array(
                    'username' => $event->getUser()->getUsername()
        ));
    }

    protected function getScope()
    {
        $form  = $this->form->getName();

        $scope = $this->request->request->get('scope', false);
        if (!$scope) {
            $scope = $this->request->query->get('scope', false);
        }
        if (!$scope) {
            $scope = $this->request->request->get("{$form}[scope]", false, true);
        }

        return !is_array($scope) ? explode(' ', $scope) : $scope;
    }
}
