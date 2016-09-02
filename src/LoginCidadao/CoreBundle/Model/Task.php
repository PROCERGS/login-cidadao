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

abstract class Task
{

    /**
     * @return string
     */
    public abstract function getName();

    /**
     * @return array in the form ['route name', ['route' => 'params']]
     */
    public abstract function getTarget();

    /**
     * @return array
     */
    public abstract function getTaskRoutes();

    /**
     * @return string
     */
    public function getSkipRoute()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public abstract function isMandatory();

    /**
     * @return int
     */
    public abstract function getPriority();

    /**
     * @param $routeName
     * @return bool
     */
    public function isTaskRoute($routeName)
    {
        return in_array($routeName, $this->getTaskRoutes());
    }

    /**
     * @param $routeName
     * @return bool
     */
    public function isSkipRoute($routeName)
    {
        return $routeName === $this->getSkipRoute();
    }

    /**
     * Returns a value that can be used to identify a task in the "skip" context.
     *
     * For example if a Task is specific to a given RP this method could return something like {TASK_NAME}_{RP_ID}
     *
     * @return string
     */
    public function getSkipId()
    {
        return $this->getName();
    }
}
