<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Service;


use LoginCidadao\CoreBundle\Model\Task;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TasksManager
{
    const TASK_SKIPPED_PREFIX_SESSION_KEY = 'tasks.skipped.';
    /** @var SessionInterface */
    private $session;

    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param Task[] $tasks
     * @return Task
     */
    public function getNextTask(array $tasks = [], $routeName = null)
    {
        if (count($tasks) <= 0) {
            return null;
        }

        usort(
            $tasks,
            function (Task $a, Task $b) {
                return $a->getPriority() < $b->getPriority();
            }
        );

        $filtered = array_filter(
            $tasks,
            function (Task $task) use ($routeName) {
                if ($task->isMandatory() || $routeName === null) {
                    return true;
                }
                $skipped = $task->isSkipRoute($routeName);

                if ($skipped) {
                    $this->markSkipped($task);
                }

                return !$skipped;
            }
        );

        $firstMandatory = null;
        foreach ($filtered as $task) {
            if ($task->isMandatory()) {
                return $task;
            }
            if ($task->isSkipRoute($routeName)) {
                continue;
            }
        }

        return reset($filtered);
    }

    /**
     * @param Task $task
     * @return string
     */
    private function getSessionKey(Task $task)
    {
        return self::TASK_SKIPPED_PREFIX_SESSION_KEY.$task->getSkipId();
    }

    /**
     * @param Task $task
     */
    private function markSkipped(Task $task)
    {
        if (!($this->session instanceof SessionInterface)) {
            return;
        }

        $this->session->set($this->getSessionKey($task), true);
    }

    /**
     * @param Task $task
     * @return bool true if task is skipped
     */
    public function checkTaskSkipped(Task $task)
    {
        if (!$this->session) {
            return false;
        }

        return $this->session->has($this->getSessionKey($task));
    }
}
