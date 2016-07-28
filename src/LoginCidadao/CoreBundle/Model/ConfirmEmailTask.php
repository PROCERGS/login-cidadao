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

class ConfirmEmailTask extends Task
{
    /** @var bool */
    private $isMandatory;

    /**
     * @return string
     */
    public function getName()
    {
        return 'lc.task.confirm_email';
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return ['task_confirm_email', []];
    }

    public function getTaskRoutes()
    {
        return [
            'task_confirm_email',
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
     * @return ConfirmEmailTask
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
        return 100;
    }
}
