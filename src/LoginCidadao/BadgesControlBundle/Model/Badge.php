<?php

namespace LoginCidadao\BadgesControlBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class Badge
 * @package LoginCidadao\BadgesControlBundle\Model
 *
 * @JMS\ExclusionPolicy("all")
 */
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

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("data")
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("name")
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("namespace")
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

}
