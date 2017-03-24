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

use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\Event;

class PhoneChangedEvent extends Event
{
    /** @var PersonInterface */
    private $person;

    /**
     * PhoneChangedEvent constructor.
     * @param PersonInterface $person
     */
    public function __construct(PersonInterface $person)
    {
        $this->person = $person;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }
}
