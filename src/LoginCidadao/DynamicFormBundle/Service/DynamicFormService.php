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
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\CityRepository;
use LoginCidadao\CoreBundle\Entity\CountryRepository;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Entity\StateRepository;
use LoginCidadao\CoreBundle\Form\Type\DynamicForm\DynamicPersonType;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\SelectData;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicFormService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var FormFactory */
    private $formFactory;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var TaskStackManagerInterface */
    private $taskStackManager;

    /**
     * DynamicFormService constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     * @param FormFactory $formFactory
     * @param UserManagerInterface $userManager
     * @param TaskStackManagerInterface $taskStackManager
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        FormFactory $formFactory,
        UserManagerInterface $userManager,
        TaskStackManagerInterface $taskStackManager
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->taskStackManager = $taskStackManager;
    }

    public function getDynamicFormData(PersonInterface $person, Request $request, $scope)
    {
        $url = $this->taskStackManager->getTargetUrl($this->taskStackManager->getNextTask()->getTarget());

        $placeOfBirth = new SelectData();
        $placeOfBirth->getFromObject($person);

        $data = new DynamicFormData();
        $data->setPerson($person)
            ->setRedirectUrl($url)
            ->setScope($scope)
            ->setPlaceOfBirth($placeOfBirth);

        $this->dispatchProfileEditInitialize($request, $person);

        return $data;
    }

    public function getForm(Request $request, PersonInterface $person)
    {
        $scope = $request->get('scope', null);
        $requestedScope = explode(' ', $scope);

        $data = $this->getDynamicFormData($person, $scope);

        $builder = $this->buildForm($this->getFormBuilder($data), $person, $requestedScope);

        return $builder->getForm();
    }

    public function buildForm(FormInterface $builder, PersonInterface $person, $scopes)
    {
        foreach ($scopes as $scope) {
            $this->addField($builder, $scope, $person);
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

        if ($placeOfBirth instanceof SelectData) {
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

    private function addField(FormInterface $formBuilder, $scope, PersonInterface $person)
    {
        switch ($scope) {
            case 'name':
            case 'surname':
            case 'full_name':
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'firstname',
                    null,
                    array('required' => true)
                );
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'surname',
                    null,
                    array('required' => true)
                );
                break;
            case 'cpf':
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'cpf',
                    null,
                    array(
                        'required' => true,
                        'attr' => array(
                            'class' => 'form-control cpf',
                        ),
                    )
                );
                break;
            case 'email':
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'email',
                    null,
                    array('required' => true)
                );
                break;
            case 'id_cards':
                // TODO: fix ID Card
                break;
            case 'phone_number':
            case 'mobile':
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'mobile',
                    'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
                    array(
                        'required' => true,
                        'label' => 'person.form.mobile.label',
                        'attr' => [
                            'class' => 'form-control intl-tel',
                            'placeholder' => 'person.form.mobile.placeholder',
                        ],
                        'label_attr' => ['class' => 'intl-tel-label'],
                        'format' => PhoneNumberFormat::E164,
                    )
                );
                break;
            case 'birthdate':
                $this->addPersonField(
                    $formBuilder,
                    $person,
                    'birthdate',
                    'birthday',
                    array(
                        'required' => true,
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                        'label' => 'form.birthdate',
                        'translation_domain' => 'FOSUserBundle',
                        'attr' => array('pattern' => '[0-9/]*', 'class' => 'form-control birthdate'),
                    )
                );
                break;
            case 'city':
                $placeOfBirthLevel = 'city';
                $this->addPlaceOfBirth($formBuilder, $placeOfBirthLevel);
                break;
            case 'state':
                $placeOfBirthLevel = 'state';
                $this->addPlaceOfBirth($formBuilder, $placeOfBirthLevel);
                break;
            case 'country':
                $placeOfBirthLevel = 'country';
                $this->addPlaceOfBirth($formBuilder, $placeOfBirthLevel);
                break;
            case 'addresses':

                $addressAction = $request->get('address_action', 'edit');
                $new = $addressAction === 'new' ? true : false;
                $this->addAddresses($formBuilder, $person, $new);
                break;
            default:
                break;
        }
    }

    private function getPersonForm(
        FormInterface $formBuilder,
        PersonInterface $person
    ) {
        if ($formBuilder->has('person') === false) {
            $formBuilder->add(
                'person',
                new DynamicPersonType(),
                array('label' => false)
            );
        }

        return $formBuilder->get('person');
    }

    private function addPersonField(
        FormInterface $formBuilder,
        PersonInterface $person,
        $field,
        $type = null,
        $options = array()
    ) {
        $personForm = $this->getPersonForm($formBuilder, $person);
        $personForm->add($field, $type, $options);
    }

    private function addPlaceOfBirth(FormInterface $formBuilder, $level)
    {
        $formBuilder->add(
            'placeOfBirth',
            'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
            array(
                'level' => $level,
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
            )
        );

        return;
    }

    private function addAddresses(
        FormBuilderInterface $formBuilder,
        PersonInterface $person,
        $new = true
    ) {
        $addresses = $person->getAddresses();
        $address = new PersonAddress();
        $address->setLocation(new SelectData());
        if ($new === false && $addresses->count() > 0) {
            $address = $addresses->last();
            $city = $address->getCity();
            if ($city instanceof City) {
                $state = $city->getState();
                $country = $state->getCountry();
                $address->getLocation()->setCity($city)
                    ->setState($state)->setCountry($country);
            }
        }
        $formBuilder->getData()->setAddress($address);

        $formBuilder->add(
            'address',
            'LoginCidadao\CoreBundle\Form\Type\PersonAddressFormType',
            array('label' => false)
        );
    }

    private function getFormBuilder(DynamicFormData $data)
    {
        return $this->formFactory->createBuilder(
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            $data,
            ['cascade_validation' => true]
        );
    }

    /**
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @return array|null
     */
    private function getAuthorizedScope(PersonInterface $person, ClientInterface $client)
    {
        $authorization = $this->getAuthorizationRepository()->findOneBy(
            [
                'client' => $client,
                'person' => $person,
            ]
        );

        if (!$authorization instanceof Authorization) {
            return null;
        }

        return $authorization->getScope();
    }

    public function getClient($clientId)
    {
        $parsing = explode('_', $clientId, 2);
        if (count($parsing) !== 2) {
            throw new \InvalidArgumentException('Invalid client_id.');
        }

        $client = $this->em
            ->getRepository('LoginCidadaoOAuthBundle:Client')
            ->findOneBy(
                [
                    'id' => $parsing[0],
                    'randomId' => $parsing[1],
                ]
            );

        if (!$client instanceof ClientInterface) {
            throw new NotFoundHttpException('Client not found');
        }

        return $client;
    }

    /**
     * @return AuthorizationRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getAuthorizationRepository()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:Authorization');
    }

    /**
     * @return CityRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getCityRepository()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:City');
    }

    /**
     * @return StateRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getStateRepository()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:State');
    }

    /**
     * @return CountryRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getCountryRepository()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:Country');
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
}
