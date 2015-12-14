<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Controller\DocumentController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use LoginCidadao\CoreBundle\EventListener\ProfileEditListner;
use LoginCidadao\CoreBundle\Form\Type\DocFormType;

class DocumentController extends BaseController
{

    /**
     * @Route("/person/documents/general", name="lc_documents_general")
     * @Template()
     */
    public function generalAction(Request $request)
    {
        $response = parent::generalAction($request);

        $repo = $this->get('meurs.entities.person_meurs.repository');

        $response['personMeuRS'] = $repo->findBy(array(
            'person' => $this->getUser()
        ));

        return $response;
    }
}
