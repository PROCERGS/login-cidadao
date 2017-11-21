<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TranslateScopeEvent extends Event
{
    /** @var string */
    private $scope;

    /** @var string */
    private $translation;

    /**
     * TranslateScopeEvent constructor.
     * @param string $scope
     */
    public function __construct($scope)
    {
        $this->scope = $scope;
        $this->translation = null;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     * @return TranslateScopeEvent
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTranslated()
    {
        return $this->translation !== null;
    }
}
