<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationControlBundle\Validator\Constraints as Validators;

/**
 * @ORM\Table(name="id_card",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_document", columns={"state_id", "value"}),
 *     @ORM\UniqueConstraint(name="unique_document", columns={"state_id", "person_id"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\IdCardRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"state","value"}, message="This document is already in use.")
 * @UniqueEntity(fields={"state","person"}, message="You already have an ID Card in this state.")
 * @Validators\IdCard
 */
class IdCard implements IdCardInterface
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="idCards")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\State")
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
     * @Assert\Regex(
     *     pattern="/^[A-Za-z0-9]+$/",
     *     message="This field accepts only letters and numbers"
     * )
     */
    protected $value;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setState(State $state)
    {
        $this->state = $state;
        return $this;
    }

    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;
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

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

}
