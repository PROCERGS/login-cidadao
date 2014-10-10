<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;

class PersonAddressController extends Controller
{

    /**
     * @Route("/person/addresses", name="lc_person_addresses")
     * @Template()
     */
    public function listAction()
    {
        $person = $this->getUser();

        return compact('person');
    }

    /**
     * @Route("/person/addresses/new", name="lc_person_addresses_new")
     * @Template()
     */
    public function newAddressAction(Request $request)
    {
        $address = new PersonAddress();
        $form = $this->createForm('lc_person_address', $address);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $address->setPerson($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_person_addresses'));
        }

        return compact('form');
    }

    /**
     * @ Route("/person/addresses/new", name="lc_person_addresses_new")
     * @Template()
     */
    public function deleteAction()
    {

    }

}
