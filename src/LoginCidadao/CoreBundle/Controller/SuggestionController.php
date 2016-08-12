<?php
/**
 * @author Guilherme Donato
 */

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Entity\ClientSuggestion;

class SuggestionController extends Controller
{

    /**
     * @Route("/authorizations/suggestion", name="lc_suggestions_new_service")
     * @Template()
     */
    public function newClientSuggestionAction(Request $request)
    {
        $sugg = new ClientSuggestion();
        $formBuilder = $this->createFormBuilder($sugg);
        $formBuilder->add(
            'text',
            'Symfony\Component\Form\Extension\Core\Type\TextareaType'
        );
        $form = $formBuilder->getForm();

        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $sugg->setPerson($this->getUser());
                $em->persist($sugg);
                $em->flush();
                $bag = $request->getSession()->getFlashBag();
                $translator = $this->get('translator');
                $bag->add(
                    'success',
                    $translator->trans('client.suggestion.registered')
                );
            }
        }

        return $this->redirect($this->generateUrl('lc_authorization_list'));
    }
}
