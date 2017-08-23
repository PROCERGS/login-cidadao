<?php

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Form\Type\SuggestionFormType;
use LoginCidadao\OAuthBundle\Entity\Client;
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
        /** @var Client[] $allApps */
        $allApps = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client')->findAll();
        $user = $this->getUser();

        $apps = [];
        // Filtering off authorized apps
        foreach ($allApps as $app) {
            if ($user->hasAuthorization($app) || !$app->isVisible()) {
                continue;
            }
            $apps[] = $app;
        }

        $suggestion = $this->handleSuggestion($request);
        if ($suggestion instanceof RedirectResponse) {
            return $suggestion;
        } else {
            $form = $suggestion->createView();
        }

        $suggestions = $this->getCurrentUserSuggestions();
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

    private function getCurrentUserSuggestions()
    {
        $suggestions = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:ClientSuggestion')
            ->findBy(['person' => $this->getUser()], ['createdAt' => 'desc'], 6);

        return $suggestions;
    }
}
