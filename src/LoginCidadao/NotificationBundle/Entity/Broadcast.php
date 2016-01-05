<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\CoreBundle\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use LoginCidadao\NotificationBundle\Handler\NotificationHandler;

/**
 * Notification Broadcast entity
 *
 * @ORM\Table(name="broadcast")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Broadcast
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinTable(
     *     name="broadcast_person",
     *     joinColumns={@ORM\JoinColumn(name="broadcast_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="personid", referencedColumnName="id")}
     * )
     * @var \Doctrine\Common\Collections\Collection
     */
    private $receivers;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="broadcasts")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\NotificationBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\Column(name="html_tpl", type="text", nullable=true)
     */
    private $htmlTemplate;
    
    /**
     * @ORM\Column(name="mail_template", type="text", nullable=true)
     */
    private $mailTemplate;    
    
    /**
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(name="short_text", type="string", nullable=true)
     */
    private $shortText;
    
    /**
     * @ORM\Column(name="sent", type="boolean", nullable=true)
     */
    private $sent;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($var)
    {
        $this->category = $var;
        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson($var)
    {
        $this->person = $var;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getReceivers()
    {
        return $this->receivers;
    }

    public function setReceivers($receivers)
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getHtmlTemplate()
    {
        return $this->htmlTemplate;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // public function setHtmlTemplate($htmlTemplate)
    // {
    //     $this->htmlTemplate = $htmlTemplate;
    //     return $this;
    // }

    public function setHtmlTemplate(ArrayCollection $placeholders, $title, $shortText) {
      $this->htmlTemplate = NotificationHandler::renderHtmlByCategory($this->getCategory(), $placeholders, $title, $shortText);
      $this->mailTemplate = NotificationHandler::renderHtmlByCategory($this->getCategory(), $placeholders, $title, $shortText);
      return $this;
    }
    
    public function setMailTemplate($var) {
        $this->mailTemplate = $var;
        return $this;
    }    
    
    public function getMailTemplate() {
        return $this->mailTemplate;
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getShortText()
    {
        return $this->shortText;
    }

    public function setShortText($shortText)
    {
        $this->shortText = $shortText;
        return $this;
    }
    
    public function getSent()
    {
        return $this->sent;
    }

    public function setSent($sent)
    {
        $this->sent = $sent;
        return $this;
    }

}
