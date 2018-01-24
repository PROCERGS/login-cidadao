<?php

namespace LoginCidadao\BadgesControlBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Interface BadgeInterface
 * @package LoginCidadao\BadgesControlBundle\Model
 *
 * @JMS\ExclusionPolicy("all")
 */
interface BadgeInterface
{

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("namespace")
     * @return string
     */
    public function getNamespace();

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("name")
     * @return string
     */
    public function getName();

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("data")
     * @return mixed
     */
    public function getData();
}
