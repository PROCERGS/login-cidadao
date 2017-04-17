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


use Symfony\Component\Routing\RouterInterface;

class RouteTaskTarget implements TaskTargetInterface
{
    /** @var string */
    private $route;

    /** @var array */
    private $parameters;

    /**
     * RouteTaskTarget constructor.
     * @param string $route
     * @param array $parameters
     */
    public function __construct($route, array $parameters = [])
    {
        $this->route = $route;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
