<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\EventListener;

use LoginCidadao\TOSBundle\Model\TOSManager;
use LoginCidadao\TOSBundle\Exception\TermsNotAgreedException;
use Symfony\Bundle\AsseticBundle\Controller\AsseticController;
use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CheckAgreementListener
{
    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var HttpUtils */
    private $httpUtils;

    /** @var TOSManager */
    private $termsManager;

    public function __construct(SecurityContextInterface $securityContext,
                                TOSManager $termsManager, HttpUtils $httpUtils)
    {
        $this->securityContext = $securityContext;
        $this->termsManager    = $termsManager;
        $this->httpUtils       = $httpUtils;
    }

    public function onFilterController(FilterControllerEvent $event)
    {
        if (!$this->shouldCheckTerms($event)) {
            return;
        }

        $request = $event->getRequest();

        if ($this->httpUtils->checkRequestPath($request, 'tos_agree') ||
            $this->httpUtils->checkRequestPath($request, 'tos_terms') ||
            $request->attributes->get('_controller') == 'LoginCidadaoTOSBundle:Agreement' ||
            $request->attributes->get('_controller') == 'LoginCidadaoTOSBundle:TermsOfService:showLatest') {
            return;
        }

        $user = $this->securityContext->getToken()->getUser();
        if (!$this->termsManager->hasAgreedToLatestTerms($user)) {
            throw new TermsNotAgreedException();
        }
    }

    private function shouldCheckTerms(FilterControllerEvent $event)
    {
        $hasToken = $this->securityContext->getToken() instanceof TokenInterface;
        if (!$hasToken || false === $this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }
        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            return false;
        }

        $controller = $event->getController();

        if (!is_array($controller)) {
            return false;
        }

        if ($controller[0] instanceof AsseticController ||
            $controller[0] instanceof ProfilerController) {
            return false;
        }

        return true;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof TermsNotAgreedException)) {
            return;
        }

        $route    = 'tos_agree';
        $request  = $event->getRequest();
        $request->getSession()->set('tos_continue_url',
            $request->getRequestUri());
        $response = $this->httpUtils->createRedirectResponse($request, $route);
        $event->setResponse($response);
    }
}
