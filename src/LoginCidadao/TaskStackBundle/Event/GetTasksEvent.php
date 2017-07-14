<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Event;

use LoginCidadao\TaskStackBundle\Model\IntentTask;
use LoginCidadao\TaskStackBundle\Model\TaskInterface;
use LoginCidadao\TaskStackBundle\Model\UrlTaskTarget;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class GetTasksEvent extends Event
{
    /** @var TaskStackManagerInterface */
    private $stackManager;

    /** @var Request */
    private $request;

    /**
     * GetTasksEvent constructor.
     * @param TaskStackManagerInterface $stackManager
     * @param Request $request
     */
    public function __construct(TaskStackManagerInterface $stackManager, Request $request)
    {
        $this->stackManager = $stackManager;
        $this->request = $request;
    }

    /**
     * @param TaskInterface $task
     * @return $this
     */
    public function addTask(TaskInterface $task)
    {
        $intent = null;
        if (!$this->stackManager->hasIntentTask()) {
            $intent = new IntentTask(new UrlTaskTarget($this->getRequest()->getUri()));
        }
        $this->stackManager->addNotSkippedTaskOnce($task, $intent);

        return $this;
    }

    /**
     * Only add the task if the Stack is empty.
     *
     * If the Stack contains only one IntentTask task, then the Stack will be considered empty.
     *
     * @param TaskInterface $task
     * @return $this
     */
    public function addTaskIfStackEmpty(TaskInterface $task)
    {
        $count = $this->stackManager->countTasks();

        if ($count >= 2) {
            return $this;
        }

        if ($count === 0) {
            $this->addTask($task);
        }

        $currentTask = $this->stackManager->getCurrentTask();
        if ($count === 1 && $currentTask instanceof IntentTask) {
            $this->addTask($task);
        }

        return $this;
    }

    /**
     * Sets the task as not skipped and adds it using <code>addTask</code>.
     * @param TaskInterface $task
     * @return $this
     */
    public function forceAddUniqueTask(TaskInterface $task)
    {
        $this->stackManager->setTaskSkipped($task, false);
        $this->addTask($task);

        return $this;
    }

    public function setTaskSkipped(TaskInterface $task, $skipped = true)
    {
        $this->stackManager->setTaskSkipped($task, $skipped);

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
