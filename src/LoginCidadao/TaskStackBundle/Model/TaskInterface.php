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
     * @param array|null $routes
     * @return TaskInterface
     */
    public function setRoutes(array $routes = null);

    /**
     * @return TaskTargetInterface
     */
    public function getTarget();

    /**
     * @param TaskTargetInterface $target
     * @return TaskInterface
     */
    public function setTarget(TaskTargetInterface $target);

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
     * Returns a value that can be used to identify a task in the "skip" context.
     *
     * If a Task is specific to a given RP this method could return something like {TASK_NAME}_{RP_ID}
     *
     * @return string
     */
    public function getSkipId();
}
