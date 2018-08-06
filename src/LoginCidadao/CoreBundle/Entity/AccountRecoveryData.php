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
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints as LCAssert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AccountRecoveryData
 *
 * @ORM\Table(name="account_recovery_data")
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\AccountRecoveryDataRepository")
 */
class AccountRecoveryData
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
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @LCAssert\Email(strict=true)
     * @Assert\NotBlank(message="person.validation.email.not_blank")
     */
    private $email;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(name="mobile", type="phone_number", nullable=true)
     * @LCAssert\E164PhoneNumber(
     *     maxMessage="person.validation.mobile.length.max",
     *     groups={"Registration", "LoginCidadaoRegistration", "Dynamic", "Profile", "LoginCidadaoProfile"}
     * )
     * @LCAssert\MobilePhoneNumber(
     *     missing9thDigit="person.validation.mobile.9thDigit",
     *     groups={"Registration", "LoginCidadaoRegistration", "Dynamic", "Profile", "LoginCidadaoProfile"}
     * )
     * @AssertPhoneNumber(
     *     type="mobile",
     *     groups={"Registration", "LoginCidadaoRegistration", "Dynamic", "Profile", "LoginCidadaoProfile"}
     * )
     */
    private $mobile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

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
     *
     * @return AccountRecoveryData
     */
    public function setPerson(PersonInterface $person): AccountRecoveryData
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return PersonInterface
     */
    public function getPerson(): PersonInterface
    {
        return $this->person;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return AccountRecoveryData
     */
    public function setEmail(string $email): AccountRecoveryData
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set mobile
     *
     * @param PhoneNumber $mobile
     *
     * @return AccountRecoveryData
     */
    public function setMobile(PhoneNumber $mobile): AccountRecoveryData
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return PhoneNumber
     */
    public function getMobile(): PhoneNumber
    {
        return $this->mobile;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AccountRecoveryData
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

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return AccountRecoveryData
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
