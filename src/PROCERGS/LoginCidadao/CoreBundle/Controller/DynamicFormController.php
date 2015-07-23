<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\FOSUserEvents;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\DynamicForm\DynamicPersonType;
use PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard;
use PROCERGS\LoginCidadao\CoreBundle\Model\IdCardInterface;
use PROCERGS\LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\DynamicFormEvents;

class DynamicFormController extends Controller
{
    const LOCATION_FORM_LEVEL_CITY    = 'city';
    const LOCATION_FORM_LEVEL_STATE   = 'state';
    const LOCATION_FORM_LEVEL_COUNTRY = 'country';

    /**
     * @Route("/client/{clientId}/dynamic-form", name="client_dynamic_form")
     * @Template()
     */
    public function editAction(Request $request, $clientId)
    {
        $client = $this->parseClient($clientId);
        if (!($client instanceof Client)) {
            throw $this->createNotFoundException("Client not found.");
        }

        $person          = $this->getUser();
        $authorizedScope = $person->getClientScope($client);
        $requestedScope  = explode(' ', $request->get('scope', null));

        $scope = $this->intersectScopes($authorizedScope, $requestedScope);

        $waitEmail = count($scope) === 1 && array_search('email', $scope) !== false;
        if ($waitEmail && $person->getEmailConfirmedAt() instanceof \DateTime) {
            //return $this->redirect($request->get('redirect_url', null));
        }

        $placeOfBirth = new SelectData();
        $placeOfBirth->getFromObject($person);

        $data = new DynamicFormData();
        $data->setPerson($person)
            ->setRedirectUrl($request->get('redirect_url', null))
            ->setScope($request->get('scope', null))
            ->setPlaceOfBirth($placeOfBirth);

        $dispatcher = $this->getDispatcher();

        $event = new \FOS\UserBundle\Event\GetResponseUserEvent($person,
            $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $formBuilder = $this->createFormBuilder($data,
            array('cascade_validation' => true));
        foreach ($scope as $curr) {
            $this->addField($formBuilder, $curr, $person);
        }
        $formBuilder->add('redirect_url', 'hidden')
            ->add('scope', 'hidden');

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $event = new \FOS\UserBundle\Event\FormEvent($form, $request);
            $dispatcher->dispatch(DynamicFormEvents::POST_FORM_VALIDATION,
                $event);

            $event = new \FOS\UserBundle\Event\FormEvent($form->get('person'),
                $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $em           = $this->getDoctrine()->getManager();
            $person       = $formBuilder->getData()->getPerson();
            $address      = $formBuilder->getData()->getAddress();
            $idCard       = $formBuilder->getData()->getIdCard();
            $placeOfBirth = $formBuilder->getData()->getPlaceOfBirth();

            if ($placeOfBirth instanceof SelectData) {
                $placeOfBirth->toObject($person);
            }

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);

            if ($address instanceof PersonAddress) {
                $address->setPerson($person);
                $em->persist($address);
            }

            if ($idCard instanceof IdCardInterface) {
                $this->getValidationHandler()->persistIdCard($form, $request);
                $em->persist($idCard);
            }

            $response = $this->redirect($formBuilder->getData()->getRedirectUrl());
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED,
                new \FOS\UserBundle\Event\FilterUserResponseEvent($person,
                $request, $response));

            $em->flush();

            $dispatcher->dispatch(DynamicFormEvents::POST_FORM_EDIT, $event);

            $dispatcher->dispatch(DynamicFormEvents::PRE_REDIRECT, $event);

            $person = $userManager->findUserByUsername($person->getUsername());
            if (!$waitEmail || $waitEmail && $person->getConfirmationToken() === null) {
                return $response;
            } else {
                $params = $request->query->all();

                $params['clientId'] = $clientId;

                $url = $this->generateUrl('client_dynamic_form', $params);
                return $this->redirect($url);
            }
        }

        $viewData = compact('client', 'scope', 'authorizedScope');

        $viewData['form'] = $form->createView();
        return $viewData;
    }

    /**
     * @Route("/wait/validate/email", name="wait_valid_email")
     * @Method("GET")
     * @Template()
     */
    public function checkEmailAction(Request $request)
    {
        $user = $this->getUser();

        if ($user->getConfirmationToken() === null) {
            $result = true;
        } else {
            $updatedAt = \DateTime::createFromFormat('Y-m-d H:i:s',
                    $request->get('updated_at'));

            if (!($updatedAt instanceof \DateTime)) {
                $updatedAt = new \DateTime();
            }

            $em     = $this->getDoctrine()->getManager();
            $person = $user->waitUpdate($em, $updatedAt);
            $result = $person->getConfirmationToken() === null;
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/dynamic-form/location", name="dynamic_form_location")
     * @Template()
     */
    public function locationFormAction(Request $request)
    {
        $country = $this->getCountry($request);
        $state   = $this->getState($request);
        $city    = $this->getCity($request);

        $locationData = new \PROCERGS\LoginCidadao\CoreBundle\Model\SelectData();

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

        $level = $request->get('level');
        $data  = new DynamicFormData();
        $data->setPlaceOfBirth($locationData);

        $formBuilder = $this->createFormBuilder($data,
            array('cascade_validation' => true));
        $this->addPlaceOfBirth($formBuilder, $level);

        return array('form' => $formBuilder->getForm()->createView());
    }

    /**
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\Person
     */
    public function getUser()
    {
        return parent::getUser();
    }

    private function addField(Request $request,
                              FormBuilderInterface $formBuilder, $scope,
                              Person $person)
    {
        $placeOfBirthLevel = '';
        switch ($scope) {
            case 'surname':
            case 'full_name':
                $this->addPersonField($formBuilder, $person, 'firstname', null,
                    array('required' => true));
                $this->addPersonField($formBuilder, $person, 'surname', null,
                    array('required' => true));
                break;
            case 'cpf':
                $this->addPersonField($formBuilder, $person, 'cpf', null,
                    array(
                    'required' => true,
                    'attr' => array(
                        'class' => 'form-control cpf'
                    )
                ));
                break;
            case 'email':
                $this->addPersonField($formBuilder, $person, 'email', null,
                    array('required' => true));
                break;
            case 'id_cards':
                $this->addIdCard($formBuilder, $person);
                break;
            case 'mobile':
                $this->addPersonField($formBuilder, $person, 'mobile', null,
                    array('required' => true));
                break;
            case 'birthdate':
                $this->addPersonField($formBuilder, $person, 'birthdate',
                    'birthday',
                    array(
                    'required' => true,
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'label' => 'form.birthdate',
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array('pattern' => '[0-9/]*', 'class' => 'form-control birthdate')
                ));
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
                $new           = $addressAction === 'new' ? true : false;
                $this->addAddresses($formBuilder, $person, $new);
                break;
            default:
                break;
        }
    }

    private function getPersonForm(FormBuilderInterface $formBuilder,
                                   Person $person)
    {
        if ($formBuilder->has('person') === false) {
            $formBuilder->add('person', new DynamicPersonType(),
                array('label' => false));
        }

        return $formBuilder->get('person');
    }

    private function addAddresses(FormBuilderInterface $formBuilder,
                                  Person $person, $new = true)
    {
        $addresses = $person->getAddresses();
        $address   = new PersonAddress();
        $address->setLocation(new SelectData());
        if ($new === false && $addresses->count() > 0) {
            $address = $addresses->last();
            $city    = $address->getCity();
            if ($city instanceof City) {
                $state   = $city->getState();
                $country = $state->getCountry();
                $address->getLocation()->setCity($city)
                    ->setState($state)->setCountry($country);
            }
        }
        $formBuilder->getData()->setAddress($address);

        $formBuilder->add('address', 'lc_person_address',
            array('label' => false));
    }

    private function addIdCard(Request $request,
                               FormBuilderInterface $formBuilder, Person $person)
    {
        $state    = $this->getStateFromRequest($request);
        $formData = $formBuilder->getData();
        foreach ($person->getIdCards() as $idCard) {
            if ($idCard->getState()->getId() === $state->getId()) {
                $formData->setIdCard($idCard);
                break;
            }
        }

        if (!($formData->getIdCard() instanceof IdCard)) {
            $validationHandler = $this->getValidationHandler();
            $idCard            = $validationHandler->instantiateIdCard($state);
            $idCard->setPerson($person);
            $formData->setIdCard($idCard);
        }

        $formBuilder->add('idcard', 'lc_idcard_form', array('label' => false));
    }

    private function addPersonField(FormBuilderInterface $formBuilder,
                                    Person $person, $field, $type = null,
                                    $options = array())
    {
        $personForm = $this->getPersonForm($formBuilder, $person);
        $personForm->add($field, $type, $options);
    }

    /**
     * @return ValidationHandler
     */
    private function getValidationHandler()
    {
        return $this->get('validation.handler');
    }

    /**
     * @param Request $request
     * @return State
     */
    private function getStateFromRequest(Request $request)
    {
        $repo = $this->getDoctrine()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:State');

        $stateId = $request->get('id_card_state_id', null);
        if ($stateId !== null) {
            $state = $repo->find($stateId);
        }
        $stateAcronym = $request->get('id_card_state', null);
        if ($stateAcronym !== null) {
            $state = $repo->findOneByAcronym($stateAcronym);
        }

        return $state;
    }

    private function addPlaceOfBirth(FormBuilderInterface $formBuilder, $level)
    {

        $formBuilder->add('placeOfBirth', 'lc_location',
            array(
            'level' => $level,
            'city_label' => 'Place of birth - City',
            'state_label' => 'Place of birth - State',
            'country_label' => 'Place of birth - Country',
        ));
        return;
    }

    private function getCity(Request $request)
    {
        $id = $request->get('city');
        if ($id === null) {
            return null;
        }
        $repo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
        return $repo->find($id);
    }

    private function getState(Request $request)
    {
        $id = $request->get('state');
        if ($id === null) {
            return null;
        }
        $repo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
        return $repo->find($id);
    }

    private function getCountry(Request $request)
    {
        $id = $request->get('country');
        if ($id === null) {
            return null;
        }
        $repo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:Country');
        return $repo->find($id);
    }

    private function fetchPlaceOfBirthData($data)
    {
        $cityRepo   = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
        $stateRepo  = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
        $coutryRepo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:Country');

        $result = array();

        if (array_key_exists('country', $data)) {
            $result['country'] = $coutryRepo->find($data['country']);
        }
        if (array_key_exists('state', $data)) {
            $result['state'] = $stateRepo->find($data['state']);
        }
        if (array_key_exists('city', $data)) {
            $result['city'] = $cityRepo->find($data['city']);
        }

        return $result;
    }

    private function parseClient($clientId)
    {
        $parsing = explode('_', $clientId, 2);
        if (count($parsing) > 1) {
            $params = array(
                'id' => $parsing[0],
                'randomId' => $parsing[1]
            );
        } else {
            $params = array('id' => $clientId);
        }

        return $this->getDoctrine()
                ->getRepository('PROCERGSOAuthBundle:Client')
                ->findOneBy($params);
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private function getDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    private function getScopeAsArray($scope)
    {
        if ($scope === null) {
            $scope = array();
        }
        if (!is_array($scope)) {
            if (preg_match("/[^ ]+ [^ ]+/", $scope) === 1) {
                $scope = explode(' ', $scope);
            } else {
                $scope = array($scope);
            }
        }

        return $scope;
    }

    private function intersectScopes($authorizedScope, $requestedScope)
    {
        $authorizedScope = $this->getScopeAsArray($authorizedScope);
        $requestedScope  = $this->getScopeAsArray($requestedScope);

        $result = array_intersect($authorizedScope, $requestedScope);

        if (empty($result) && array_search('email', $requestedScope) !== false) {
            $result[] = 'email';
        }

        return $result;
    }
}
