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


use LoginCidadao\CoreBundle\Model\Task;

class TestTask extends Task
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
     * TestTask constructor.
     * @param TestTaskBuilder $builder
     */
    public function __construct(TestTaskBuilder $builder)
    {
        $this->name = $builder->getName();
        $this->target = $builder->getTarget();
        $this->taskRoutes = $builder->getTaskRoutes();
        $this->mandatory = $builder->isMandatory();
        $this->priority = $builder->getPriority();
        $this->skipRoute = $builder->getSkipRoute();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array in the form ['route name', ['route' => 'params']]
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return array
     */
    public function getTaskRoutes()
    {
        return $this->taskRoutes;
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getSkipRoute()
    {
        return $this->skipRoute;
    }
}
