<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\Event;

class PhoneChangedEvent extends Event
{
    /** @var PersonInterface */
    private $person;

    /** @var PhoneNumber */
    private $oldPhone;

    /**
     * PhoneChangedEvent constructor.
     * @param PersonInterface $person
     * @param PhoneNumber $oldPhone
     */
    public function __construct(PersonInterface $person, PhoneNumber $oldPhone = null)
    {
        $this->person = $person;
        $this->oldPhone = $oldPhone;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function getOldPhone()
    {
        return $this->oldPhone;
    }
}
