<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Service;

class TestTaskBuilder
{
    /** @var string */
    private $name;

    /** @var array */
    private $target;

    /** @var array */
    private $taskRoutes;

    /** @var bool */
    private $mandatory;

    /** @var int */
    private $priority;

    /** @var string */
    private $skipRoute;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TestTaskBuilder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param array $target
     * @return TestTaskBuilder
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return array
     */
    public function getTaskRoutes()
    {
        return $this->taskRoutes;
    }

    /**
     * @param array $taskRoutes
     * @return TestTaskBuilder
     */
    public function setTaskRoutes($taskRoutes)
    {
        $this->taskRoutes = $taskRoutes;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param boolean $mandatory
     * @return TestTaskBuilder
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return TestTaskBuilder
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function build()
    {
        return new TestTask($this);
    }

    /**
     * @return string
     */
    public function getSkipRoute()
    {
        return $this->skipRoute;
    }

    /**
     * @param string $skipRoute
     * @return TestTaskBuilder
     */
    public function setSkipRoute($skipRoute)
    {
        $this->skipRoute = $skipRoute;

        return $this;
    }
}
