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
 * @ORM\Table(name="person")
 * @ORM\HasLifecycleCallbacks
 */
class BlockedPhoneNumber implements BlockedPhoneNumberInterface
{
    private $id;

    /**
     * @var PhoneNumber
     */
    private $phoneNumber;

    /**
     * @var PersonInterface
     */
    private $blockedBy;

    /**
     * @var \DateTime
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
