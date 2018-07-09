<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Twig\Extension;

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Event\TranslateScopeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ScopesExtension extends \Twig_Extension
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ScopesExtension constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(EventDispatcherInterface $dispatcher, TranslatorInterface $translator)
    {
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('scope', [$this, 'scopeFilter']),
            new \Twig_SimpleFilter('scope_has_description', [$this, 'scopeHasDescription']),
        ];
    }

    public function scopeFilter($scopes, $allowBlank = false)
    {
        $translator = $this->translateScope();
        if (is_array($scopes)) {
            $result = [];
            foreach ($scopes as $scope) {
                $result[$scope] = $translator($scope);
            }

            return $allowBlank ? $result : array_filter($result);
        }

        return $translator($scopes);
    }

    public function scopeHasDescription($scope)
    {
        $id = "scope_details.{$scope}";
        $translation = $this->translator->trans($id);

        return $translation !== $id;
    }

    private function translateScope()
    {
        return function ($scope) {
            $event = new TranslateScopeEvent($scope);
            $this->dispatcher->dispatch(LoginCidadaoCoreEvents::TRANSLATE_SCOPE, $event);
            if ($event->isTranslated()) {
                return $event->getTranslation();
            }

            $id = "scope.{$scope}";
            $translation = $this->translator->trans($id);
            if ($translation !== $id) {
                return $translation;
            }

            return $scope;
        };
    }
}
