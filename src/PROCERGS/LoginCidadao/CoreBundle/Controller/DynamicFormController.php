<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\DynamicForm\DynamicPersonType;
use PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData;

class DynamicFormController extends Controller
{

    /**
     * @Route("/client/{clientId}/dynamic-form")
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
            ->setRedirectUrl($request->get('redirect_url', null));

        $formBuilder = $this->createForm('form', $data);
        foreach ($scope as $curr) {
            $this->addField($formBuilder, $curr, $person);
        }
        $formBuilder->add('redirect_url', 'hidden');

        $formBuilder->handleRequest($request);
        if ($formBuilder->isValid()) {
            $em      = $this->getDoctrine()->getManager();
            $person  = $formBuilder->getData()->getPerson();
            $address = $formBuilder->getData()->getAddress();

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);

            if ($address instanceof PersonAddress) {
                $address->setPerson($person);
                $em->persist($address);
                $em->flush();
            }

            return $this->redirect($formBuilder->getData()->getRedirectUrl());
        }

        $form = $formBuilder->createView();

        return compact('scope', 'authorizedScope', 'form');
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
            case 'addresses':
                $this->addAddresses($formBuilder, $person);
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

    private function addAddresses(FormInterface $formBuilder, Person $person)
    {
        if (count($person->getAddresses()) > 0) {
            return;
        }

        $formBuilder->add('address', 'lc_person_address',
            array('label' => false));
    }

    private function addIdCard(FormInterface $formBuilder, Person $person)
    {
        $formBuilder->add('idcard', 'lc_idcard_form', array('label' => false));
    }

    private function addPersonField(FormInterface $formBuilder, Person $person,
                                    $field, $type = null)
    {
        $personForm = $this->getPersonForm($formBuilder, $person);
        $personForm->add($field, $type);
    }
}
