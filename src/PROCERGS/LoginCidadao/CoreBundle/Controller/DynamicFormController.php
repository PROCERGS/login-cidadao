<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\DynamicForm\DynamicPersonType;
use PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard;
use PROCERGS\LoginCidadao\CoreBundle\Model\IdCardInterface;
use PROCERGS\LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;

class DynamicFormController extends Controller
{

    /**
     * @Route("/client/{clientId}/dynamic-form", name="client_dynamic_form")
     * @Template()
     */
    public function editAction(Request $request, $clientId)
    {
        $client = $this->getDoctrine()
            ->getRepository('PROCERGSOAuthBundle:Client')
            ->find($clientId);

        $person          = $this->getUser();
        $authorizedScope = $person->getClientScope($client);

        $scope = explode(' ', $request->get('scope', null));

        $data = new DynamicFormData();
        $data->setPerson($person)
            ->setRedirectUrl($request->get('redirect_url', null))
            ->setScope($request->get('scope', null));

        $formBuilder = $this->createForm('form', $data,
            array('cascade_validation' => true));
        foreach ($scope as $curr) {
            $this->addField($formBuilder, $curr, $person);
        }
        $formBuilder->add('redirect_url', 'hidden')
            ->add('scope', 'hidden');

        $formBuilder->handleRequest($request);
        if ($formBuilder->isValid()) {
            $em      = $this->getDoctrine()->getManager();
            $person  = $formBuilder->getData()->getPerson();
            $address = $formBuilder->getData()->getAddress();
            $idCard  = $formBuilder->getData()->getIdCard();

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);

            if ($address instanceof PersonAddress) {
                $address->setPerson($person);
                $em->persist($address);
            }

            if ($idCard instanceof IdCardInterface) {
                $this->getValidationHandler()->persistIdCard($formBuilder,
                    $request);
                $em->persist($idCard);
            }

            $em->flush();

            //return $this->redirect($formBuilder->getData()->getRedirectUrl());
        }

        $form = $formBuilder->createView();

        return compact('client', 'scope', 'authorizedScope', 'form');
    }

    /**
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\Person
     */
    public function getUser()
    {
        return parent::getUser();
    }

    private function addField(FormInterface $formBuilder, $scope, Person $person)
    {
        switch ($scope) {
            case 'surname':
            case 'full_name':
                $this->addPersonField($formBuilder, $person, 'firstname');
                $this->addPersonField($formBuilder, $person, 'surname');
                break;
            case 'cpf':
                $this->addPersonField($formBuilder, $person, 'cpf');
                break;
            case 'id_cards':
                $this->addIdCard($formBuilder, $person);
                break;
            case 'email':
                $this->addPersonField($formBuilder, $person, 'email');
                break;
            case 'mobile':
                $this->addPersonField($formBuilder, $person, 'mobile');
                break;
            case 'birthdate':
                $this->addPersonField($formBuilder, $person, 'birthdate', 'text');
                break;
            case 'city':
                $this->addPersonField($formBuilder, $person, 'city',
                    'city_selector');
            case 'state':
                $this->addPersonField($formBuilder, $person, 'state',
                    'state_selector');
            case 'country':
                $this->addPersonField($formBuilder, $person, 'country',
                    'country_selector');
                break;
            case 'addresses.new':
                $this->addAddresses($formBuilder, $person, true);
                break;
            case 'addresses.edit':
                $this->addAddresses($formBuilder, $person, false);
                break;
            default:
                break;
        }
    }

    private function getPersonForm(FormInterface $formBuilder, Person $person)
    {
        if ($formBuilder->has('person') === false) {
            $formBuilder->add('person', new DynamicPersonType(),
                array('label' => false));
        }

        return $formBuilder->get('person');
    }

    private function addAddresses(FormInterface $formBuilder, Person $person,
                                  $new = true)
    {
        $addresses = $person->getAddresses();
        if ($new === false && $addresses->count() > 0) {
            $address = $addresses->last();
            $formBuilder->getData()->setAddress($address);
        }

        $formBuilder->add('address', 'lc_person_address',
            array('label' => false));
    }

    private function addIdCard(FormInterface $formBuilder, Person $person)
    {
        $state    = $this->getStateFromRequest($this->getRequest());
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

    private function addPersonField(FormInterface $formBuilder, Person $person,
                                    $field, $type = null)
    {
        $personForm = $this->getPersonForm($formBuilder, $person);
        $personForm->add($field, $type);
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

        $stateId = $request->get('idCardStateId', null);
        if ($stateId !== null) {
            $state = $repo->find($stateId);
        }
        $stateAcronym = $request->get('idCardState', null);
        if ($stateAcronym !== null) {
            $state = $repo->findOneByAcronym($stateAcronym);
        }

        return $state;
    }
}
