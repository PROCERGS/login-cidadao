<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Model;

class IntentTask extends AbstractTask
{
    /** @var UrlTaskTarget */
    protected $target;

    /**
     * IntentTask constructor.
     * @param UrlTaskTarget $target
     */
    public function __construct(UrlTaskTarget $target)
    {
        $this->target = $target;
    }

    /**
     * Intents can't be skipped, they're just the user's original intent.
     *
     * @return boolean
     */
    public function isMandatory()
    {
        return true;
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
        return $this->target->getUrl();
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return [];
    }

    /**
     * @return TaskTargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }
}
