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
use Symfony\Component\HttpFoundation\Request;

class GetTasksEvent extends Event
{
    /** @var Task[] */
    protected $tasks;

    /** @var Request */
    protected $request;

    /**
     * GetClientEvent constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->tasks = [];
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

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
