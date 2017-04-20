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

use LoginCidadao\TaskStackBundle\Model\AbstractTask;
use LoginCidadao\TaskStackBundle\Model\TaskTargetInterface;

class ConfirmEmailTask extends AbstractTask
{
    /** @var bool */
    private $isMandatory;

    /** @var TaskTargetInterface */
    private $target;

    /**
     * ConfirmEmailTask constructor.
     * @param TaskTargetInterface $target
     * @param bool $mandatory
     */
    public function __construct(TaskTargetInterface $target, $mandatory = false)
    {
        $this->target = $target;
        $this->isMandatory = $mandatory;
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
        return 'lc.task.confirm_email';
    }

    public function getRoutes()
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
     * @return TaskTargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }
}
