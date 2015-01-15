<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Entity;

use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeInterface;

class Badge implements BadgeInterface
{

    protected $namespace;
    protected $name;
    protected $data;

    public function __construct($namespace, $name, $data = null)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

}
