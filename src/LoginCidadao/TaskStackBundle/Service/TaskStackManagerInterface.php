<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Service;

use LoginCidadao\TaskStackBundle\Model\IntentTask;
use LoginCidadao\TaskStackBundle\Model\TaskInterface;
use LoginCidadao\TaskStackBundle\Model\TaskTargetInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TaskStackManagerInterface
{
    /**
     * Stacks the given $task, making sure there are no duplicates.
     *
     * @param TaskInterface $task
     * @param IntentTask|null $intentTask
     * @return TaskStackManagerInterface
     */
    public function addNotSkippedTaskOnce(TaskInterface $task, IntentTask $intentTask = null);

    /**
     * Marks a task as skipped.
     *
     * @param TaskInterface $task
     * @param bool $isSkipped
     * @return TaskStackManagerInterface
     */
    public function setTaskSkipped(TaskInterface $task, $isSkipped = true);

    /**
     * @param Request $request
     * @param Response|null $defaultResponse
     * @return Response
     */
    public function processRequest(Request $request, Response $defaultResponse = null);

    /**
     * Clears the stack
     *
     * @return void
     */
    public function emptyStack();

    /**
     * Returns the task that's on the top of the stack.
     *
     * @return TaskInterface|null
     */
    public function getCurrentTask();

    /**
     * Returns the next task.
     *
     * @return TaskInterface|null
     */
    public function getNextTask();

    /**
     * Counts how many Tasks are in the Stack
     *
     * @return int
     */
    public function countTasks();

    /**
     * Checks if the stack already have an IntentTask.
     *
     * @return boolean
     */
    public function hasIntentTask();

    /**
     * Converts a TaskTargetInterface into a string URL.
     *
     * @param TaskTargetInterface $target
     * @return string
     */
    public function getTargetUrl(TaskTargetInterface $target);
}
