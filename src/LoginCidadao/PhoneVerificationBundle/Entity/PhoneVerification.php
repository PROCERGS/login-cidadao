<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\AbstractPhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;

/**
 * PhoneVerification
 *
 * @ORM\Table(name="phone_verification")
 * @ORM\Entity(repositoryClass="LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PhoneVerification extends AbstractPhoneVerification implements PhoneVerificationInterface
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
     * @var PersonInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", unique=false)
     */
    private $person;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=false)
     */
    private $phone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="verified_at", type="datetime", nullable=true)
     */
    private $verifiedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="verification_code", type="string", length=255, nullable=true)
     */
    private $verificationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="verification_token", type="string", length=255, nullable=false)
     */
    private $verificationToken;

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
     * Set person
     *
     * @param PersonInterface $person
     * @return PhoneVerification
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set phone
     *
     * @param PhoneNumber $phone
     * @return PhoneVerification
     */
    public function setPhone(PhoneNumber $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return PhoneNumber
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set verifiedAt
     *
     * @param \DateTime $verifiedAt
     * @return PhoneVerification
     */
    public function setVerifiedAt($verifiedAt)
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    /**
     * Get verifiedAt
     *
     * @return \DateTime
     */
    public function getVerifiedAt()
    {
        return $this->verifiedAt;
    }

    /**
     * Set verificationCode
     *
     * @param string $verificationCode
     * @return PhoneVerification
     */
    public function setVerificationCode($verificationCode)
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    /**
     * Get verificationCode
     *
     * @return string
     */
    public function getVerificationCode()
    {
        return $this->verificationCode;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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

    /**
     * @return string
     */
    public function getVerificationToken()
    {
        return $this->verificationToken;
    }

    /**
     * @param string $verificationToken
     * @return PhoneVerification
     */
    public function setVerificationToken($verificationToken)
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }
}
