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
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;

/**
 * Class BlockedPhoneNumber
 *
 * @package LoginCidadao\PhoneVerificationBundle\Model
 *
 * @ORM\Entity(repositoryClass="LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumberRepository")
 * @ORM\Table(name="blocked_phone_number", indexes={
 *     @ORM\Index(name="blocked_phone_number_idx", columns={"phone_number"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class BlockedPhoneNumber implements BlockedPhoneNumberInterface
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
     * @var PhoneNumber
     *
     * @ORM\Column(name="phone_number", type="phone_number", nullable=false, unique=true)
     */
    private $phoneNumber;

    /**
     * @var PersonInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="blocked_by_person_id", referencedColumnName="id", unique=false)
     */
    private $blockedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    public function __construct(PhoneNumber $phoneNumber, PersonInterface $blockedBy, \DateTime $createdAt)
    {
        $this->phoneNumber = $phoneNumber;
        $this->blockedBy = $blockedBy;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getBlockedBy(): PersonInterface
    {
        return $this->blockedBy;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
