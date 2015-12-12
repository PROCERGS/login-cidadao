<?php

namespace LoginCidadao\BadgesControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LoginCidadao\BadgesControlBundle\Model\BadgeInterface;

class ListBearersEvent extends Event
{

    /** @var BadgeInterface */
    protected $badge = null;

    /** @var array */
    protected $count = array(
        'namespace' => array(
            'badge' => 0
        )
    );

    public function __construct(BadgeInterface $badge = null)
    {
        $this->badge = $badge;

        if ($badge !== null) {
            $this->count = array(
                $badge->getNamespace() => array(
                    $badge->getName() => 0
                )
            );
        } else {
            $this->count = array();
        }
    }

    /**
     *
     * @return BadgeInterface
     */
    public function getBadge()
    {
        return $this->badge;
    }

    public function setCount(BadgeInterface $badge, $count)
    {
        $namespace = $badge->getNamespace();
        $name = $badge->getName();
        if (!array_key_exists($namespace, $this->count)) {
            $this->count[$namespace] = array(
                $name => $count
            );
        } else {
            $this->count[$namespace][$name] = $count;
        }

        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

}
