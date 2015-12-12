<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Entity\Placeholder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class BroadcastPlaceholder
{

    protected $placeholder;
    protected $value;

    public function __construct(Placeholder $placeholder)
    {
        $this->placeholder = $placeholder;
        $this->value = $placeholder->getDefault();
    }

    /**
     * @return Placeholder
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function setPlaceholder(Placeholder $placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getValue()
    {
      return $this->value;
    }

    public function setValue($value)
    {
      $this->value = $value;
      return $this;
    }
    
    public function getName()
    {
        return $this->getPlaceholder()->getName();
    }
    
    public function getDefault()
    {
        return $this->getPlaceholder()->getDefault();
    }
    
    public function getId()
    {
        return $this->getPlaceholder()->getId();
    }

    public function persist(EntityManager $em)
    {
        foreach ($this->getPlaceholders() as $placeholder) {
            $em->persist($placeholder);
        }
    }

}
