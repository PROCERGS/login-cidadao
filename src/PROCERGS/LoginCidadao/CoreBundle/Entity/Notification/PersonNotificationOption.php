<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PersonOption
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PersonNotificationOption
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Determines if this category of notification should be sent by email
     *
     * @var boolean
     * @ORM\Column(name="send_email", type="boolean")
     */
    private $sendEmail;

    /**
     * Determines if this category of notification should be sent via push
     * notification
     *
     * @var boolean
     * @ORM\Column(name="send_push", type="boolean")
     */
    private $sendPush;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person", inversedBy="notificationOptions")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PersonNotificationOption
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    public function setCategory($var)
    {
        $this->category = $var;
        return $this;
    }
    
    public function setPerson($var) 
    {
        $this->person = $var;
        return $this;
    }
    
    public function setSendEmail($var)
    {
        $this->sendEmail = $var;
        return $this;
    }
    
    public function setSendPush($var)
    {
        $this->sendPush = $var;
        return $this;
    }

}
