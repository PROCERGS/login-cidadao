<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use LoginCidadao\CoreBundle\EventListener\ProfileEditListener;

class DocumentController extends Controller
{

    /**
     * @Route("/person/documents", name="lc_documents")
     * @Template()
     */
    public function documentsAction(Request $request)
    {
        return $this->generalAction($request);
    }

    /**
     * @Route("/person/documents/general", name="lc_documents_general")
     * @Template()
     */
    public function generalAction(Request $request)
    {
        $user = $this->getUser();
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $form = $this->createForm($this->getParameter('lc.person_doc.form.class'), $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(ProfileEditListener::PROFILE_DOC_EDIT_SUCCESS,
                                    $event);

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success',
                                                        $translator->trans("Documents were successfully changed"));
            return $this->redirect($this->generateUrl('lc_documents'));
        }

        return array(
            'generalDocsForm' => $form->createView()
        );
    }

}
