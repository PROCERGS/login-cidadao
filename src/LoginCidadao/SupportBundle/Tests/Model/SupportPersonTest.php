<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Tests\Model;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\SupportBundle\Model\PersonalData;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SupportPersonTest extends TestCase
{
    public function testMinimumAccess()
    {
        $authChecker = $this->getAuthChecker([]);

        $phone = (new PhoneNumber())->setNationalNumber(55)->setNationalNumber(51999998888);

        /** @var MockObject|PersonInterface $person */
        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getId')->willReturn($id = 123);
        $person->expects($this->once())->method('getFirstName')->willReturn($firstName = 'John');
        $person->expects($this->once())->method('getSurname')->willReturn($lastName = 'Doe');
        $person->expects($this->once())->method('getBirthdate')->willReturn(new \DateTime());
        $person->expects($this->once())->method('getEmailConfirmedAt')->willReturn($emailVerified = new \DateTime());
        $person->expects($this->once())->method('getPasswordRequestedAt')->willReturn($lastPwReset = null);
        $person->expects($this->once())->method('getGoogleAuthenticatorSecret')->willReturn('1234');
        $person->expects($this->once())->method('isEnabled')->willReturn(true);
        $person->expects($this->once())->method('getUpdatedAt')->willReturn($lastUpdate = new \DateTime());
        $person->expects($this->once())->method('getCreatedAt')->willReturn($createdAt = new \DateTime());
        $person->expects($this->once())->method('getLastLogin')->willReturn($lastLogin = new \DateTime());
        $person->expects($this->once())->method('getMobile')->willReturn($phone);

        $supportPerson = new SupportPerson($person, $authChecker);
        $this->assertInstanceOf(PersonalData::class, $supportPerson->getBirthday());
        $this->assertNull($supportPerson->getBirthday()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getCpf());
        $this->assertNull($supportPerson->getCpf()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getEmail());
        $this->assertNull($supportPerson->getEmail()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getPhoneNumber());
        $this->assertNull($supportPerson->getPhoneNumber()->getValue());

        $this->assertSame($id, $supportPerson->getId());
        $this->assertSame($firstName, $supportPerson->getFirstName());
        $this->assertSame($lastName, $supportPerson->getLastName());
        $this->assertSame($emailVerified, $supportPerson->getEmailVerifiedAt());
        $this->assertSame($lastPwReset, $supportPerson->getLastPasswordResetRequest());
        $this->assertSame($lastUpdate, $supportPerson->getLastUpdate());
        $this->assertSame($createdAt, $supportPerson->getCreatedAt());
        $this->assertSame($lastLogin, $supportPerson->getLastLogin());
        $this->assertTrue($supportPerson->has2FA());
        $this->assertTrue($supportPerson->isEnabled());
    }

    public function testFullAccess()
    {
        $authChecker = $this->getAuthChecker([
            'ROLE_VIEW_USERS_CPF',
            'ROLE_SUPPORT_VIEW_EMAIL',
            'ROLE_SUPPORT_VIEW_PHONE',
            'ROLE_SUPPORT_VIEW_BIRTHDAY'
        ]);

        $phone = (new PhoneNumber())->setNationalNumber(55)->setNationalNumber(51999998888);

        /** @var MockObject|PersonInterface $person */
        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getId')->willReturn($id = 123);
        $person->expects($this->once())->method('getFirstName')->willReturn($firstName = 'John');
        $person->expects($this->once())->method('getSurname')->willReturn($lastName = 'Doe');
        $person->expects($this->once())->method('getBirthdate')->willReturn(new \DateTime());
        $person->expects($this->once())->method('getEmailConfirmedAt')->willReturn($emailVerified = new \DateTime());
        $person->expects($this->once())->method('getPasswordRequestedAt')->willReturn($lastPwReset = null);
        $person->expects($this->once())->method('getGoogleAuthenticatorSecret')->willReturn('1234');
        $person->expects($this->once())->method('isEnabled')->willReturn(true);
        $person->expects($this->once())->method('getUpdatedAt')->willReturn($lastUpdate = new \DateTime());
        $person->expects($this->once())->method('getCreatedAt')->willReturn($createdAt = new \DateTime());
        $person->expects($this->once())->method('getMobile')->willReturn($phone);

        $supportPerson = new SupportPerson($person, $authChecker);
        $this->assertInstanceOf(PersonalData::class, $supportPerson->getBirthday());
        $this->assertNotNull($supportPerson->getBirthday()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getCpf());
        $this->assertNotNull($supportPerson->getCpf()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getEmail());
        $this->assertNotNull($supportPerson->getEmail()->getValue());

        $this->assertInstanceOf(PersonalData::class, $supportPerson->getPhoneNumber());
        $this->assertNotNull($supportPerson->getPhoneNumber()->getValue());

        $this->assertSame($id, $supportPerson->getId());
        $this->assertSame($firstName, $supportPerson->getFirstName());
        $this->assertSame($lastName, $supportPerson->getLastName());
        $this->assertSame($emailVerified, $supportPerson->getEmailVerifiedAt());
        $this->assertSame($lastPwReset, $supportPerson->getLastPasswordResetRequest());
        $this->assertSame($lastUpdate, $supportPerson->getLastUpdate());
        $this->assertSame($createdAt, $supportPerson->getCreatedAt());
        $this->assertTrue($supportPerson->has2FA());
        $this->assertTrue($supportPerson->isEnabled());
    }

    public function testGetName()
    {
        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getId')->willReturn($id = 123);
        $supportPerson = new SupportPerson($person, $this->getAuthChecker([]));

        $this->assertEquals($id, $supportPerson->getName());

        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getId')->willReturn(123);
        $person->expects($this->once())->method('getFirstName')->willReturn($firstName = 'John');
        $supportPerson = new SupportPerson($person, $this->getAuthChecker([]));

        $this->assertEquals($firstName, $supportPerson->getName());
    }

    private function getAuthChecker(array $roles = [])
    {
        /** @var MockObject|AuthorizationCheckerInterface $authChecker */
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->expects($this->exactly(4))->method('isGranted')
            ->willReturnCallback(function ($role) use ($roles) {
                return false !== array_search($role, $roles);
            });

        return $authChecker;
    }
}
