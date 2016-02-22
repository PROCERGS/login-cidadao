<?php

namespace LoginCidadao\CoreBundle\Security\Http\Firewall;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use LoginCidadao\CoreBundle\Entity\AccessSession;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;

class LoginCidadaoListener extends UsernamePasswordFormAuthenticationListener
{
    /** @var EntityManager */
    protected $em;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var integer */
    protected $bruteForceThreshold;
    protected $container;

    public function setContainer($var)
    {
        $this->container = $var;
    }

    public function getContainer()
    {
        return $this->container;
    }

    private function getFilter(Request $request)
    {
        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'],
                    null, true));
        } else {
            $username = trim($request->get($this->options['username_parameter'],
                    null, true));
        }
        return array(
            'ip' => $request->getClientIp(),
            'username' => $username
        );
    }

    protected function attemptAuthentication(Request $request)
    {
        $options       = $this->getFilter($request);
        $accessSession = $this->registerAttempt($request);

        $request->getSession()->set(
            Security::LAST_USERNAME, $options['username']
        );

        $formType      = 'LoginCidadao\CoreBundle\Form\Type\LoginFormType';
        $check_captcha = $accessSession->getVal() >= $this->bruteForceThreshold;

        $form = $this->formFactory->create($formType, null,
            compact('check_captcha'));
        $form->handleRequest($request);
        if (!$form->isValid()) {
            $translator = $this->translator;
            throw new BadCredentialsException($translator->trans('Captcha is invalid.'));
        }
        return parent::attemptAuthentication($request);
    }

    public function setBruteForceThreshold($bruteForceThreshold)
    {
        $this->bruteForceThreshold = $bruteForceThreshold;
        return $this;
    }

    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    private function getAccessSession(Request $request)
    {
        $options = $this->getFilter($request);

        $accessSession = $this->em
            ->getRepository('LoginCidadaoCoreBundle:AccessSession')
            ->findOneBy($options);
        if (!$accessSession) {
            $accessSession = new AccessSession();
            $accessSession->fromArray($options);
        }
        return $accessSession;
    }

    private function registerAttempt(Request $request)
    {
        $accessSession = $this->getAccessSession($request);
        $accessSession->setVal($accessSession->getVal() + 1);
        $this->em->persist($accessSession);
        $this->em->flush();

        return $accessSession;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        return $this;
    }
}
