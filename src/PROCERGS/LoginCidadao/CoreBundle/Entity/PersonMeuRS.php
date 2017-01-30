<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use PROCERGS\Generic\ValidationBundle\Validator\Constraints as PROCERGSAssert;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRSRepository")
 * @UniqueEntity("person")
 * @UniqueEntity(fields="voterRegistration", errorPath="voterRegistration", message="person_rs.validation.voter_registration.already_used", groups={"LoginCidadaoRegistration", "Registration", "LoginCidadaoEmailForm", "LoginCidadaoProfile", "Dynamic", "Documents"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="person_meurs")
 * @JMS\ExclusionPolicy("all")
 */
class PersonMeuRS
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \LoginCidadao\CoreBundle\Entity\Person
     * @ORM\OneToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", cascade={"persist"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * @ORM\Column(name="nfg_access_token", type="string", length=255, nullable=true, unique=true)
     * @JMS\Since("1.0.2")
     */
    protected $nfgAccessToken;

    /**
     * @JMS\Expose
     * @JMS\Groups({"nfgprofile"})
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile", cascade={"persist"})
     * @ORM\JoinColumn(name="nfg_profile_id", referencedColumnName="id")
     * @JMS\Since("1.0.2")
     */
    protected $nfgProfile;

    /**
     * @JMS\Expose
     * @JMS\Groups({"voter_registration"})
     * @ORM\Column(name="voter_registration", type="string", length=12, nullable=true, unique=true)
     * @PROCERGSAssert\VoterRegistration
     * @JMS\Since("1.0.2")
     */
    protected $voterRegistration;

    /**
     * @JMS\Groups({"badges", "public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("deprecated_badges")
     * @return array
     */
    public function getDataValid()
    {
        $terms['cpf'] = (is_numeric($this->cpf) && strlen($this->nfgAccessToken));
        $terms['email'] = is_null($this->getConfirmationToken());
        if ($this->getNfgProfile()) {
            $terms['nfg_access_lvl'] = $this->getNfgProfile()->getAccessLvl();
            $terms['voter_registration'] = $this->getNfgProfile()->getVoterRegistrationSit()
            > 0 ? true : false;
        } else {
            $terms['nfg_access_lvl'] = 0;
            $terms['voter_registration'] = false;
        }

        return $terms;
    }

    public function setNfgAccessToken($var)
    {
        $this->nfgAccessToken = $var;

        return $this;
    }

    public function getNfgAccessToken()
    {
        return $this->nfgAccessToken;
    }

    /**
     *
     * @param \PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile $var
     * @return PersonMeuRS
     */
    public function setNfgProfile(NfgProfile $var = null) {
        $this->nfgProfile = $var;

        return $this;
    }

    /**
     * @return \PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile
     */
    public function getNfgProfile()
    {
        return $this->nfgProfile;
    }

    public function setVoterRegistration($var = null)
    {
        if (null === $var) {
            $this->voterRegistration = null;
        } else {
            $this->voterRegistration = preg_replace('/[^0-9]/', '', $var);
        }

        return $this;
    }

    public function getVoterRegistration()
    {
        return $this->voterRegistration;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }
}
