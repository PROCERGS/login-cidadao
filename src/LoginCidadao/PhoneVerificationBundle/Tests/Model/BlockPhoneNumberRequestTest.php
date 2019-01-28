<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Model;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\BlockPhoneNumberRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlockPhoneNumberRequestTest extends TestCase
{
    public function testRequest()
    {
        /** @var MockObject|PersonInterface $person */
        $person = $this->createMock(PersonInterface::class);

        /** @var MockObject|PhoneNumber $phoneNumber */
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $request = new BlockPhoneNumberRequest($person);
        $this->assertSame($person, $request->getBlockedBy());
        $this->assertNull($request->phoneNumber);

        $request->phoneNumber = $phoneNumber;

        $this->assertSame($phoneNumber, $request->phoneNumber);
    }
}
