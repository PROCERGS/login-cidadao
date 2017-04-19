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

interface TaskInterface
{
    /**
     * @return array
     */
    public function getRoutes();

    /**
     * @return TaskTargetInterface
     */
    public function getTarget();

    /**
     * @return boolean
     */
    public function isMandatory();

    /**
     * @param string $routeName
     * @return boolean
     */
    public function isTaskRoute($routeName);

    /**
     * Returns a value that can be used to identify a task. This is used to avoid repeated Tasks in the TaskStack.
     *
     * If a Task is specific to a given RP this method could return something like {TASK_NAME}_{RP_ID}
     *
     * @return string
     */
    public function getId();
}
