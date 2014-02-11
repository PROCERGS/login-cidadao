<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Handler;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    private $router;
    private $em;

    /**
     * Constructor
     * @param RouterInterface   $router
     * @param EntityManager     $em
     */
    public function __construct(RouterInterface $router, $options, EntityManager $em, Session $session)
    {
        $this->router = $router;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from AbstractAuthenticationListener.
     * @param Request        $request
     * @param TokenInterface $token
     * @return Response The response to return
     */
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {

        if (strstr($token->getUser()->getUsername(), '@') !== false) {
            $uri = $this->router->generate('lc_update_username');

            $this->session->set('referer_site_uri', $referer = $request->headers->get('referer'));

            return new RedirectResponse($uri);
        } else {
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        }
    }

}
