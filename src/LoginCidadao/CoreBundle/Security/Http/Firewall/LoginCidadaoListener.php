<?php
namespace LoginCidadao\CoreBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Entity\Person;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use LoginCidadao\CoreBundle\Entity\AccessSession;
use Symfony\Component\Security\Core\SecurityContextInterface;

class LoginCidadaoListener extends UsernamePasswordFormAuthenticationListener
{

    protected $container;

    public function setContainer($var)
    {
        $this->container = $var;
    }

    public function getContainer()
    {
        return $this->container;
    }

    private function _getVars(Request &$request)
    {
        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'], null, true));
        } else {
            $username = trim($request->get($this->options['username_parameter'], null, true));
        }
        return array(
            'ip' => $request->getClientIp(),
            'username' => $username
        );
    }

    protected function attemptAuthentication(Request $request)
    {
        $vars = $this->_getVars($request);
        $doctrine = $this->container->get('doctrine');
        $accessSession = $doctrine->getRepository('LoginCidadaoCoreBundle:AccessSession')->findOneBy($vars);
        if (! $accessSession) {
            $accessSession = new AccessSession();
            $accessSession->fromArray($vars);
        }
        $accessSession->setVal($accessSession->getVal()+1);
        $doctrine->getManager()->persist($accessSession);
        $doctrine->getManager()->flush();
        $request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $vars['username']);
        $formType = $this->container->get('lc.login.form.type');
        $formType->setVerifyCaptcha($accessSession->getVal() >= $this->container->getParameter('brute_force_threshold'));
        $form = $this->container->get('form.factory')->create($formType);
        $form->handleRequest($request);
        if (! $form->isValid()) {
            $translator = $this->container->get('translator');
            throw new BadCredentialsException($translator->trans('Captcha is invalid.'));
        }
        $b = parent::attemptAuthentication($request);
        return $b;
    }
}
