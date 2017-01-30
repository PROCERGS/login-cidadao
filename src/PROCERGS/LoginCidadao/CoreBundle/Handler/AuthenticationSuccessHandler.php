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
use LoginCidadao\CoreBundle\Entity\AccessSession;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    public function __construct(RouterInterface $router, $options,
                                EntityManager $em, Session $session,
                                HttpUtils $httpUtils)
    {
        parent::__construct($httpUtils, $options);
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
        $form = $request->get('login_form_type');
        if (isset($form['username'])) {
            $vars = array(
                'ip' => $request->getClientIp(),
                'username' =>$form['username']
            );
            $accessSession = $doctrine->getRepository('PROCERGSLoginCidadaoCoreBundle:AccessSession')->findOneBy($vars);
            if (!$accessSession) {
                $accessSession = new AccessSession();
                $accessSession->fromArray($vars);
            }
            $accessSession->setVal(0);
            $doctrine->getManager()->persist($accessSession);
            $doctrine->getManager()->flush();
        }

        // CPF check
        if ($token->getUser()->isCpfExpired()) {
            return new RedirectResponse($this->router->generate('lc_registration_cpf'));
        }

        if (strstr($token->getUser()->getUsername(), '@') !== false) {
            $uri = $this->router->generate('lc_update_username');

            $this->session->set('referer_site_uri',
                    $referer = $request->headers->get('referer'));

            return new RedirectResponse($uri);
        } else {
            $referer = $request->headers->get('referer');
            $login = $this->router->generate('fos_user_security_login', array(),
                    UrlGeneratorInterface::ABSOLUTE_URL);
            $register = $this->router->generate('fos_user_registration_register',
                    array(), UrlGeneratorInterface::ABSOLUTE_URL);
            if (strlen($referer) > 0) {
                if ($referer == $login || $referer == $register) {
                    $referer = $this->router->generate('lc_home');
                }
                $dest = $referer;
            } else {
                $dest = $this->router->generate('lc_home');
            }
            return new RedirectResponse($dest);
        }
    }

}
