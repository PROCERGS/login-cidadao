<?php

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Form\Type\SuggestionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Entity\ClientSuggestion;

class AuthorizationController extends Controller
{

    /**
     * @Route("/authorizations", name="lc_authorization_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('LoginCidadaoOAuthBundle:Client');

        $user = $this->getUser();
        $allApps = $clients->findAll();

        $apps = array();
        // Filtering off authorized apps
        foreach ($allApps as $app) {
            if ($user->hasAuthorization($app)) {
                continue;
            }
            if ($app->isVisible()) {
                $apps[] = $app;
            }
        }

        $suggestion = $this->handleSuggestion($request);
        if ($suggestion instanceof RedirectResponse) {
            return $suggestion;
        } else {
            $form = $suggestion->createView();
        }

        $suggestions = $em->getRepository('LoginCidadaoCoreBundle:ClientSuggestion')->findBy(
            ['person' => $user],
            ['createdAt' => 'desc'],
            6
        );

        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return compact('user', 'apps', 'form', 'suggestions', 'defaultClientUid');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\Form\Form|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function handleSuggestion(Request $request)
    {
        $suggestion = new ClientSuggestion();
        $form = $this->createForm(new SuggestionFormType(), $suggestion);

        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $suggestion->setPerson($this->getUser());

            $em->persist($suggestion);
            $em->flush();

            $translator = $this->get('translator');
            $this->addFlash(
                'success',
                $translator->trans('client.suggestion.registered')
            );

            return $this->redirect($this->generateUrl('lc_authorization_list'));
        }

        return $form;
    }
}
