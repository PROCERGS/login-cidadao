<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use LoginCidadao\CoreBundle\DynamicFormEvents;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\StateRepository;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\DynamicFormBundle\Form\DynamicFormBuilder;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicFormService implements DynamicFormServiceInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var TaskStackManagerInterface */
    private $taskStackManager;

    /** @var DynamicFormBuilder */
    private $dynamicFormBuilder;

    /**
     * DynamicFormService constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     * @param UserManagerInterface $userManager
     * @param TaskStackManagerInterface $taskStackManager
     * @param DynamicFormBuilder $dynamicFormBuilder
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        UserManagerInterface $userManager,
        TaskStackManagerInterface $taskStackManager,
        DynamicFormBuilder $dynamicFormBuilder
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->taskStackManager = $taskStackManager;
        $this->dynamicFormBuilder = $dynamicFormBuilder;
    }

    public function getDynamicFormData(PersonInterface $person, Request $request, $scope)
    {
        $url = $this->taskStackManager->getTargetUrl($this->taskStackManager->getNextTask()->getTarget());

        $placeOfBirth = new LocationSelectData();
        $placeOfBirth->getFromObject($person);

        $data = new DynamicFormData();
        $data->setPerson($person)
            ->setRedirectUrl($url)
            ->setScope($scope)
            ->setPlaceOfBirth($placeOfBirth)
            ->setIdCardState($this->getStateFromRequest($request));

        $this->dispatchProfileEditInitialize($request, $person);

        return $data;
    }

    public function buildForm(FormInterface $form, PersonInterface $person, array $scopes)
    {
        foreach ($scopes as $scope) {
            $this->dynamicFormBuilder->addFieldFromScope($form, $scope, $person);
        }
        $form->add('redirect_url', 'hidden')
            ->add('scope', 'hidden');

        return $form;
    }

    public function processForm(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if (!$form->isValid()) {
            return $form;
        }

        $this->dispatchFormEvent($form, $request, DynamicFormEvents::POST_FORM_VALIDATION);
        $event = null;
        if ($form->has('person')) {
            $event = $this->dispatchFormEvent($form->get('person'), $request, FOSUserEvents::PROFILE_EDIT_SUCCESS);
        }

        /** @var DynamicFormData $data */
        $data = $form->getData();
        $person = $data->getPerson();
        $address = $data->getAddress();
        $idCard = $data->getIdCard();
        $placeOfBirth = $data->getPlaceOfBirth();

        if ($placeOfBirth instanceof LocationSelectData) {
            $placeOfBirth->toObject($person);
        }

        $this->userManager->updateUser($person);

        if ($address instanceof PersonAddress) {
            $address->setPerson($person);
            $this->em->persist($address);
        }

        if ($idCard instanceof IdCardInterface) {
            $this->em->persist($idCard);
        }

        $this->taskStackManager->setTaskSkipped($this->taskStackManager->getCurrentTask());
        $response = new RedirectResponse($data->getRedirectUrl());
        $response = $this->taskStackManager->processRequest($request, $response);
        $this->dispatchProfileEditCompleted($person, $request, $response);
        $this->em->flush();

        if ($form->has('person')) {
            $this->dispatcher->dispatch(DynamicFormEvents::POST_FORM_EDIT, $event);
            $this->dispatcher->dispatch(DynamicFormEvents::PRE_REDIRECT, $event);
        }

        return $form;
    }

    public function getClient($clientId)
    {
        $parsing = explode('_', $clientId, 2);
        if (count($parsing) !== 2) {
            throw new \InvalidArgumentException('Invalid client_id.');
        }

        $client = $this->em->getRepository('LoginCidadaoOAuthBundle:Client')
            ->findOneBy(['id' => $parsing[0], 'randomId' => $parsing[1]]);

        if (!$client instanceof ClientInterface) {
            throw new NotFoundHttpException('Client not found');
        }

        return $client;
    }

    private function dispatchProfileEditInitialize(Request $request, PersonInterface $person)
    {
        $event = new GetResponseUserEvent($person, $request);
        $this->dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        return $event;
    }

    private function dispatchFormEvent(FormInterface $form, Request $request, $eventName)
    {
        $event = new FormEvent($form, $request);
        $this->dispatcher->dispatch($eventName, $event);

        return $event;
    }

    private function dispatchProfileEditCompleted(PersonInterface $person, Request $request, Response $response)
    {
        $event = new FilterUserResponseEvent($person, $request, $response);
        $this->dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);

        return $event;
    }

    /**
     * @param Request $request
     * @return State
     */
    private function getStateFromRequest(Request $request)
    {
        /** @var StateRepository $repo */
        $repo = $this->em->getRepository('LoginCidadaoCoreBundle:State');

        $stateId = $request->get('id_card_state_id', null);
        if ($stateId !== null) {
            /** @var State $state */
            $state = $repo->find($stateId);

            return $state;
        }

        $stateAcronym = $request->get('id_card_state', null);
        if ($stateAcronym !== null) {
            /** @var State $state */
            $state = $repo->findOneBy(['acronym' => $stateAcronym]);

            return $state;
        }

        return null;
    }

    public function getLocationDataFromRequest(Request $request)
    {
        $country = $this->getLocation($request, 'country');
        $state = $this->getLocation($request, 'state');
        $city = $this->getLocation($request, 'city');

        $locationData = new LocationSelectData();

        if ($city instanceof City) {
            $locationData->setCity($city)
                ->setState($city->getState())
                ->setCountry($city->getState()->getCountry());
        } elseif ($state instanceof State) {
            $locationData->setCity(null)
                ->setState($state)
                ->setCountry($state->getCountry());
        } elseif ($country instanceof Country) {
            $locationData->setCity(null)
                ->setState(null)
                ->setCountry($country);
        }

        $data = new DynamicFormData();
        $data->setPlaceOfBirth($locationData);

        return $data;
    }

    private function getLocationRepository($type)
    {
        $repo = null;
        switch ($type) {
            case 'city':
            case 'state':
            case 'country':
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:'.ucfirst($type));
                break;
        }

        return $repo;
    }

    private function getLocation(Request $request, $type)
    {
        $id = $request->get($type);
        if ($id === null) {
            return null;
        }
        $repo = $this->getLocationRepository($type);

        return $repo->find($id);
    }
}
