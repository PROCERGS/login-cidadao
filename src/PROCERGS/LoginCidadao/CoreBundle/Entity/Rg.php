<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * City
 *
 * @ORM\Table(name="rg")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Rg
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
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Uf")
     * @ORM\JoinColumn(name="uf_id", referencedColumnName="id")
     */
    protected $uf;

    /**
     * @Assert\Length(min=1,max="80")
     * @ORM\Column(name="issuer", type="string", length=80)
     * @Groups({"rg"})
     */
    protected $issuer;

    /**
     * @Assert\Length(min=1,max="20")
     * @ORM\Column(name="val",type="string", length=20)
     * @Groups({"rg"})
     */
    protected $val;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function setUf($var)
    {
        $this->uf = $var;
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

    public function getUf()
    {
        return $this->uf;
    }

    public function setVal($var)
    {
        $this->val = $var;
        return $this;
    }

    public function getVal()
    {
        return $this->val;
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
