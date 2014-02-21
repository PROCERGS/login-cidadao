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
use PROCERGS\LoginCidadao\CoreBundle\Entity\AcessSession;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    private $router;
    private $em;
    
    protected $container;
    
    public function setContainer($var)
    {
        $this->container = $var;
    }
    
    public function getContainer()
    {
        return $this->container;
    }

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
        $doctrine = $this->container->get('doctrine');
        $vars = array(
            'ip' => $request->getClientIp(),
            'username' => $token->getUser()->getUsername()
        );
        $accessSession = $doctrine->getRepository('PROCERGSLoginCidadaoCoreBundle:AcessSession')->findOneBy($vars);
        if (! $accessSession) {
            $accessSession = new AcessSession();
            $accessSession->fromArray($vars);
        }
        $accessSession->setVal(0);
        $doctrine->getManager()->persist($accessSession);
        $doctrine->getManager()->flush();
        
        if (strstr($token->getUser()->getUsername(), '@') !== false) {
            $uri = $this->router->generate('lc_update_username');

            $this->session->set('referer_site_uri', $referer = $request->headers->get('referer'));

            return new RedirectResponse($uri);
        } else {
            $referer = $request->headers->get('referer');
            $a = $this->router->generate('fos_user_security_login', array(), true);
            if (strlen($referer) > 0 && $referer != $a) {
                $dest = $referer;
            } else {
                $dest = $this->router->generate('lc_home');
            }
            return new RedirectResponse($dest);
        }
    }

}
