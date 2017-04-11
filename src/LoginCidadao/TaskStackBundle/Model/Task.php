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

abstract class Task implements TaskInterface
{
    /** @var array */
    private $routes;

    /** @var TaskTargetInterface */
    private $target;

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array|null $routes
     * @return TaskInterface
     */
    public function setRoutes(array $routes = null)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @return TaskTargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param TaskTargetInterface $target
     * @return TaskInterface
     */
    public function setTarget(TaskTargetInterface $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @param string $routeName
     * @return boolean
     */
    public function isTaskRoute($routeName)
    {
        return in_array($routeName, $this->getRoutes());
    }
}
