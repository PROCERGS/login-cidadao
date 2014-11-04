<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use PROCERGS\LoginCidadao\NotificationBundle\Entity\Broadcast;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class BroadcastSettings
{

    protected $broadcast;
    protected $placeholders;
    protected $template;
    protected $receivers;
    protected $category;

    public function __construct(Broadcast $broadcast)
    {        
        $this->broadcast = $broadcast;
        $this->template = $broadcast->getCategory()->getHtmlTemplate();
        $this->placeholders = new ArrayCollection();        
        $this->receivers = $broadcast->getReceivers();
    }

    /**
     * @return ArrayCollection
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    public function setPlaceholders(ArrayCollection $placeholders)
    {
        $this->placeholders = $placeholders;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    public function getReceivers()
    {
        return $this->receivers;
    }
    
    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
    
     public function setCategory(String $category)
    {
        $this->$category = $category;
        return $this;
    }


    /**
     * @return Broadcast
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    public function setBroadcast(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;
        return $this;
    }

    public function persist(EntityManager $em)
    {
        foreach ($this->getPlaceholders() as $placeholder) {
            $em->persist($placeholder);
        }
    }

}
