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

abstract class AbstractTask implements TaskInterface
{
    /** @var array */
    protected $routes = [];

    /**
     * @param string $routeName
     * @return boolean
     */
    public function isTaskRoute($routeName)
    {
        return in_array($routeName, $this->getRoutes());
    }
}
