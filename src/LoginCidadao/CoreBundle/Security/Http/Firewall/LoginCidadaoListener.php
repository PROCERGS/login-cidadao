<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security\Http\Firewall;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Exception\RecaptchaException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use LoginCidadao\CoreBundle\Entity\AccessSession;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;

class LoginCidadaoListener extends UsernamePasswordFormAuthenticationListener
{
    /** @var EntityManager */
    private $em;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TranslatorInterface */
    private $translator;

    /** @var integer */
    private $bruteForceThreshold;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        HttpUtils $httpUtils,
        $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options = array(),
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null,
        $csrfTokenManager = null,
        EntityManagerInterface $em
    ) {
        parent::__construct($tokenStorage, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey,
            $successHandler, $failureHandler, $options, $logger, $dispatcher, $csrfTokenManager);
        $this->em = $em;
    }


    private function getFilter(Request $request)
    {
        if ($this->options['post_only']) {
            $username = trim(
                $request->request->get(
                    $this->options['username_parameter'],
                    null,
                    true
                )
            );
        } else {
            $username = trim(
                $request->get(
                    $this->options['username_parameter'],
                    null,
                    true
                )
            );
        }

        return array(
            'ip' => $request->getClientIp(),
            'username' => $username,
        );
    }

    protected function attemptAuthentication(Request $request)
    {
        $options = $this->getFilter($request);
        $accessSession = $this->registerAttempt($request);

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $options['username']
        );

        $formType = 'LoginCidadao\CoreBundle\Form\Type\LoginFormType';
        $check_captcha = $accessSession->getVal() >= $this->bruteForceThreshold;

        $form = $this->formFactory->create($formType, null, compact('check_captcha'));
        $form->handleRequest($request);
        if (!$form->isValid()) {
            $translator = $this->translator;
            foreach ($form->getErrors() as $error) {
                if ($error->getOrigin()->getName() === 'recaptcha') {
                    throw new RecaptchaException($error->getMessage());
                }
                throw new BadCredentialsException($translator->trans($error->getMessage()));
            }
            throw new BadCredentialsException();
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
