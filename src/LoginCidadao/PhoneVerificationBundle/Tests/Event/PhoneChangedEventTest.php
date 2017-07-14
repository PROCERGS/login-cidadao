<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Event;

use LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent;

class PhoneChangedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEventChangePhone()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Entity\Person');
        $phone = $this->getMock('libphonenumber\PhoneNumber');
        $event = new PhoneChangedEvent($person, $phone);

        $this->assertEquals($person, $event->getPerson());
        $this->assertEquals($phone, $event->getOldPhone());
    }


    public function testEventSetPhone()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Entity\Person');
        $event = new PhoneChangedEvent($person, null);

        $this->assertEquals($person, $event->getPerson());
        $this->assertNull($event->getOldPhone());
    }
}
