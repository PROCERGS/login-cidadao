<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Task;

use LoginCidadao\TaskStackBundle\Model\AbstractTask;
use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;
use LoginCidadao\TaskStackBundle\Model\TaskTargetInterface;

class CompleteUserInfoTask extends AbstractTask
{
    /** @var string */
    private $clientId;

    /** @var RouteTaskTarget */
    private $target;

    /** @var string */
    private $scope = '';

    /** @var string */
    private $nonce = '';

    /**
     * CompleteUserInfoTask constructor.
     * @param string $clientId
     * @param string|array $scope
     * @param string $nonce
     */
    public function __construct($clientId, $scope, $nonce = null)
    {
        if (!$clientId) {
            throw new \InvalidArgumentException("Missing client_id");
        }
        if (!$scope) {
            throw new \InvalidArgumentException("Missing scope");
        }
        $this->clientId = $clientId;
        $this->nonce = $nonce;
        $this->setScope($scope);
        $this->target = new RouteTaskTarget(
            'dynamic_form',
            ['client_id' => $this->getClientId(), 'scope' => $this->getScope()]
        );
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return [
            'dynamic_form',
            'fos_user_registration_confirm',
            'wait_valid_email',
            'dynamic_form_skip',
            'dynamic_form_location',
        ];
    }

    /**
     * @return TaskTargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return false;
    }

    /**
     * Returns a value that can be used to identify a task. This is used to avoid repeated Tasks in the TaskStack.
     *
     * If a Task is specific to a given RP this method could return something like {TASK_NAME}_{RP_ID}
     *
     * @return string
     */
    public function getId()
    {
        $id = [
            'lc.task.complete_userinfo',
            $this->clientId,
            $this->nonce,
        ];

        return implode('_', array_filter($id));
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
    private function setScope($scope)
    {
        $scope = !is_array($scope) ? $scope : implode(' ', $scope);

        $this->scope = $scope;

        return $this;
    }
}
