<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class CommonFormType extends AbstractType
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;
    protected $translator;
    protected $router;

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    public function getUser()
    {
        if (!$this->tokenStorage) {
            throw new \LogicException('Token Storage is not available.');
        }
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function setTranslator(TranslatorInterface $var)
    {
        $this->translator = $var;
    }

    public function setRouter(RouterInterface $var)
    {
        $this->router = $var;
    }

    public function generateUrl(
        $route,
        $parameters = array(),
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        return $this->router->generate($route, $parameters, $referenceType);
    }
}
