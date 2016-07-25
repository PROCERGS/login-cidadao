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
}
