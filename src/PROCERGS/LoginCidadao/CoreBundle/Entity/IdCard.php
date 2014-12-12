<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="id_card",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_document", columns={"state_id", "value"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\CoreBundle\Entity\IdCardRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"state","value"}, message="This document is already in use.")
 */
class IdCard
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person", inversedBy="idCards")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $state;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="80")
     * @ORM\Column(name="issuer", type="string", length=80)
     */
    protected $issuer;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="20")
     * @ORM\Column(name="value",type="string", length=20)
     */
    protected $value;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function setState($var)
    {
        $this->state = $var;
        return $this;
    }

    public function setPerson($var)
    {
        $this->person = $var;
        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setValue($var)
    {
        $this->value = $var;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setIssuer($var)
    {
        $this->issuer = $var;
        return $this;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

}
