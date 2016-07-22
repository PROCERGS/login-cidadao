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

use LoginCidadao\CoreBundle\Model\Task;
use Symfony\Component\EventDispatcher\Event;

class GetTasksEvent extends Event
{
    /** @var Task[] */
    protected $tasks;

    /**
     * GetClientEvent constructor.
     * @param \LoginCidadao\CoreBundle\Model\Task[] $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * @return Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param array|Task[] $tasks
     */
    public function setTasks(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * @param Task $task
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;
    }
}
