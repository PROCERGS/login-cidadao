<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
    public function newAddressAction()
    {
        $form = $this->createForm('lc_person_address');

        return compact('form');
    }

}
