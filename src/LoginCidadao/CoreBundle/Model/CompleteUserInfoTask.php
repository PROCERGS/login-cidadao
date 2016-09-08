<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Model;

class CompleteUserInfoTask extends Task
{
    /** @var bool */
    private $isMandatory;

    /** @var string */
    private $clientId;

    /** @var string */
    private $scope = '';

    public function __construct($clientId)
    {
        if (null === $clientId) {
            throw new \InvalidArgumentException("Missing client_id");
        }
        $this->clientId = $clientId;
        $this->setIsMandatory(false);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lc.task.complete_userinfo';
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return ['client_dynamic_form', ['clientId' => $this->clientId, 'scope' => $this->scope]];
    }

    public function getTaskRoutes()
    {
        return [
            'client_dynamic_form',
            'fos_user_registration_confirm',
            'wait_valid_email',
        ];
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * @param boolean $isMandatory
     * @return CompleteUserInfoTask
     */
    public function setIsMandatory($isMandatory)
    {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 70;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string|array $scope
     * @return CompleteUserInfoTask
     */
    public function setScope($scope)
    {
        $scope = !is_array($scope) ?: implode(' ', $scope);

        $this->scope = $scope;

        return $this;
    }

    public function getSkipRoute()
    {
        return 'dynamic_form_skip';
    }

    public function getSkipId()
    {
        return $this->getName().'_'.$this->getClientId();
    }


}
