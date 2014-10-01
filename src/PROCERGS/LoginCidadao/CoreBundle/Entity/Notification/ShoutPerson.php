<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Notification
 *
 * @ORM\Table(name="shout_person")
 * @ORM\Entity
 */
class ShoutPerson
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */    
    protected $person;
    
    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Shout", inversedBy="persons")
     * @ORM\JoinColumn(name="shout_id", referencedColumnName="id")
     */    
    protected $shout;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
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

    public function getShout()
    {
        return $this->shout;
    }

    public function setShout($var)
    {
        $this->shout = $var;
        return $this;
    }
}
