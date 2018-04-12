<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use LoginCidadao\CoreBundle\Entity\AccessSession;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * Constructor
     * @param HttpUtils $httpUtils
     * @param EntityManagerInterface $em
     * @param array $options
     */
    public function __construct(
        HttpUtils $httpUtils,
        EntityManagerInterface $em,
        $options
    ) {
        parent::__construct($httpUtils, $options);
        $this->em = $em;
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from AbstractAuthenticationListener.
     * @param Request $request
     * @param TokenInterface $token
     * @return Response The response to return
     */
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $form = $request->get('login_form_type');
        if (isset($form['username'])) {
            $vars = array(
                'ip' => $request->getClientIp(),
                'username' => $form['username'],
            );

            /** @var AccessSession|null $accessSession */
            $accessSession = $this->em->getRepository('LoginCidadaoCoreBundle:AccessSession')->findOneBy($vars);
            if (!$accessSession instanceof AccessSession) {
                $accessSession = new AccessSession();
                $accessSession->fromArray($vars);
            }
            $accessSession->setVal(0);
            $this->em->persist($accessSession);
            $this->em->flush();
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
