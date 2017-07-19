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
use libphonenumber\PhoneNumberFormat;
use LoginCidadao\CoreBundle\DynamicFormEvents;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\StateRepository;
use LoginCidadao\CoreBundle\Form\Type\DynamicForm\DynamicPersonType;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;
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

    /** @var ValidationHandler */
    private $validationHandler;

    /**
     * DynamicFormService constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     * @param UserManagerInterface $userManager
     * @param TaskStackManagerInterface $taskStackManager
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        UserManagerInterface $userManager,
        TaskStackManagerInterface $taskStackManager
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->taskStackManager = $taskStackManager;
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

    public function buildForm(FormInterface $builder, PersonInterface $person, array $scopes)
    {
        foreach ($scopes as $scope) {
            $this->addFieldFromScope($builder, $scope, $person);
        }
        $builder->add('redirect_url', 'hidden')
            ->add('scope', 'hidden');

        return $builder;
    }

    public function processForm(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if (!$form->isValid()) {
            return $form;
        }

        $this->dispatchPostFormValidation($request, $form);
        $event = $this->dispatchProfileEditSuccess($request, $form);

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

    private function addFieldFromScope(FormInterface $form, $scope, PersonInterface $person)
    {
        switch ($scope) {
            case 'name':
            case 'surname':
            case 'full_name':
                $this->addRequiredPersonField($form, 'firstname');
                $this->addRequiredPersonField($form, 'surname');
                break;
            case 'cpf':
                $this->addRequiredPersonField($form, 'cpf', 'form-control cpf');
                break;
            case 'email':
                $this->addRequiredPersonField($form, 'email');
                break;
            case 'id_cards':
                $this->addIdCard($form, $person);
                break;
            case 'phone_number':
            case 'mobile':
                $this->addPhoneField($form);
                break;
            case 'birthdate':
                $this->addBirthdayField($form);
                break;
            case 'city':
                $placeOfBirthLevel = 'city';
                $this->addPlaceOfBirth($form, $placeOfBirthLevel);
                break;
            case 'state':
                $placeOfBirthLevel = 'state';
                $this->addPlaceOfBirth($form, $placeOfBirthLevel);
                break;
            case 'country':
                $placeOfBirthLevel = 'country';
                $this->addPlaceOfBirth($form, $placeOfBirthLevel);
                break;
            case 'addresses':
                $this->addAddresses($form);
                break;
            default:
                break;
        }
    }

    private function getPersonForm(FormInterface $form)
    {
        if ($form->has('person') === false) {
            $form->add('person', new DynamicPersonType(), ['label' => false]);
        }

        return $form->get('person');
    }

    private function addRequiredPersonField(FormInterface $form, $field, $cssClass = null)
    {
        $options = ['required' => true];

        if ($cssClass) {
            $options['attr'] = ['class' => $cssClass];
        }

        $this->getPersonForm($form)->add(
            $field,
            null,
            $options
        );
    }

    private function addPhoneField(FormInterface $form)
    {
        $this->getPersonForm($form)->add(
            'mobile',
            'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
            [
                'required' => true,
                'label' => 'person.form.mobile.label',
                'attr' => ['class' => 'form-control intl-tel', 'placeholder' => 'person.form.mobile.placeholder'],
                'label_attr' => ['class' => 'intl-tel-label'],
                'format' => PhoneNumberFormat::E164,
            ]
        );
    }

    private function addBirthdayField(FormInterface $form)
    {
        $this->getPersonForm($form)->add(
            'birthdate',
            'birthday',
            [
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'label' => 'form.birthdate',
                'translation_domain' => 'FOSUserBundle',
                'attr' => ['pattern' => '[0-9/]*', 'class' => 'form-control birthdate'],
            ]
        );
    }

    private function addPlaceOfBirth(FormInterface $form, $level)
    {
        $form->add(
            'placeOfBirth',
            'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
            [
                'level' => $level,
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
            ]
        );

        return;
    }

    private function addAddresses(FormInterface $form)
    {
        $address = new PersonAddress();
        $address->setLocation(new LocationSelectData());
        $form->getData()->setAddress($address);

        $form->add(
            'address',
            'LoginCidadao\CoreBundle\Form\Type\PersonAddressFormType',
            ['label' => false]
        );
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

    private function getFormEvent(Request $request, FormInterface $form)
    {
        return new FormEvent($form, $request);
    }

    private function dispatchPostFormValidation(Request $request, FormInterface $form)
    {
        $event = $this->getFormEvent($request, $form);
        $this->dispatcher->dispatch(DynamicFormEvents::POST_FORM_VALIDATION, $event);

        return $event;
    }

    private function dispatchProfileEditSuccess(Request $request, FormInterface $form)
    {
        if (!$form->has('person')) {
            return null;
        }

        $event = $this->getFormEvent($request, $form->get('person'));
        $this->dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

        return $event;
    }

    private function dispatchProfileEditCompleted(PersonInterface $person, Request $request, Response $response)
    {
        $event = new FilterUserResponseEvent($person, $request, $response);
        $this->dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);

        return $event;
    }

    private function addIdCard(FormInterface $form, PersonInterface $person)
    {
        /** @var DynamicFormData $formData */
        $formData = $form->getData();
        $state = $formData->getIdCardState();
        foreach ($person->getIdCards() as $idCard) {
            if ($idCard->getState()->getId() === $state->getId()) {
                $formData->setIdCard($idCard);
                break;
            }
        }

        if (!($formData->getIdCard() instanceof IdCardInterface)) {
            $idCard = $this->validationHandler->instantiateIdCard($state);
            $idCard->setPerson($person);
            $formData->setIdCard($idCard);
        }

        $form->add('idcard', 'lc_idcard_form', ['label' => false]);
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
        switch ($type) {
            case 'city':
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:City');
                break;
            case 'state':
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:State');
                break;
            case 'country':
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:Country');
                break;
            default:
                throw new \InvalidArgumentException("Invalid location type '{$type}'");
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
