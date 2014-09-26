<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Notification
 * @deprecated since version 1.0.2
 * @ ORM\Table()
 * @ ORM\Entity()
 */
class InteractiveNotification extends Notification\Notification
{

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=255)
     */
    protected $target;

    /**
     * @var array
     *
     * @ORM\Column(name="parameters", type="array")
     */
    protected $parameters;

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters()
    {
        if (!is_array($this->parameters)) {
            return array();
        }
        return $this->parameters;
    }

    public function resolveTarget(RouterInterface $router)
    {
        try {
            return $router->generate($this->getTarget(), $this->getParameters(),
                    RouterInterface::NETWORK_PATH);
        } catch (RouteNotFoundException $e) {
            return $this->getTarget();
        }
    }

}
