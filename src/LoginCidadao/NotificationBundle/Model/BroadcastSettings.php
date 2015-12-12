<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Entity\Broadcast;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class BroadcastSettings
{

    protected $broadcast;
    protected $placeholders;
    protected $template;
    protected $receivers;
    protected $category;
    protected $title;
    protected $shortText;

    public function __construct(Broadcast $broadcast)
    {        
        $this->broadcast = $broadcast;
        $this->template = $broadcast->getCategory()->getHtmlTemplate();
        $this->placeholders = new ArrayCollection();        
        $this->receivers = $broadcast->getReceivers();        
        $this->title = $broadcast->getCategory()->getDefaultTitle();
        $this->shortText = $broadcast->getCategory()->getDefaultShortText();
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
    
     public function setCategory($category)
    {
        $this->$category = $category;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->broadcast->getTitle();
    }
    
     public function setTitle($title)
    {
        $this->broadcast->setTitle($title);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getShortText()
    {
        return $this->broadcast->getShortText();
    }
    
     public function setShortText($shortText)
    {
        $this->broadcast->setShortText($shortText);
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
