<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use libphonenumber\PhoneNumber;

/**
 * NfgProfile
 *
 * @ORM\Table(name="nfg_profile")
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class NfgProfile
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"nfgprofile"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"nfgprofile"})
     */
    protected $cpf;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"nfgprofile"})
     */
    protected $email;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"nfgprofile"})
     */
    protected $birthdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"nfgprofile"})
     */
    protected $mobile;

    /**
     * @ORM\Column(name="access_lvl",type="integer")
     * @Groups({"nfgprofile"})
     */
    protected $accessLvl;

    /**
     * @ORM\Column(name="voter_reg", type="string", nullable=true)
     * @Groups({"nfgprofile"})
     */
    protected $voterRegistration;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     * @Groups({"nfgprofile"})
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="voter_reg_sit", type="integer", nullable=true)
     * @Groups({"nfgprofile"})
     */
    protected $voterRegistrationSit;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function setName($var)
    {
        $this->name = $var;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setCpf($var)
    {
        $this->cpf = $var;
        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setEmail($var)
    {
        $this->email = $var;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setBirthdate($var)
    {
        $this->birthdate = $var;
        return $this;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setMobile($var)
    {
        $this->mobile = $var;
        return $this;
    }

    /**
     * @return PhoneNumber
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    public function setAccessLvl($var)
    {
        $this->accessLvl = $var;
        return $this;
    }

    public function getAccessLvl()
    {
        return $this->accessLvl;
    }

    public function setVoterRegistration($var)
    {
        $this->voterRegistration = $var;
        return $this;
    }

    public function getVoterRegistration()
    {
        return $this->voterRegistration;
    }

    public function setVoterRegistrationSit($var)
    {
        $this->voterRegistrationSit = $var;
        return $this;
    }

    public function getVoterRegistrationSit()
    {
        return $this->voterRegistrationSit;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt($updatedAt = null)
    {
        if ($updatedAt instanceof \DateTime) {
            $this->updatedAt = $updatedAt;
        } else {
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
