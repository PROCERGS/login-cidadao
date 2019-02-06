<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Tests\Service;

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Entity\SentEmailRepository;
use LoginCidadao\SupportBundle\Exception\PersonNotFoundException;
use LoginCidadao\SupportBundle\Model\PersonalData;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use LoginCidadao\SupportBundle\Service\SupportHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SupportHandlerTest extends TestCase
{
    public function testGetSupportPerson()
    {
        $id = 123;
        $person = $this->createMock(Person::class);

        $handler = new SupportHandler($this->getAuthChecker(), $this->getPersonRepo($id, $person),
            $this->getSentEmailRepo());
        $supportPerson = $handler->getSupportPerson($id);

        $this->assertInstanceOf(SupportPerson::class, $supportPerson);
    }

    public function testGetNonExistingSupportPerson()
    {
        $this->expectException(PersonNotFoundException::class);
        $id = 123;

        $handler = new SupportHandler($this->getAuthChecker(), $this->getPersonRepo($id, null),
            $this->getSentEmailRepo());
        $handler->getSupportPerson($id);
    }

    public function testGetInitialMessage()
    {
        $id = 123;
        $sentEmail = $this->createMock(SentEmail::class);

        $handler = new SupportHandler($this->getAuthChecker(), $this->getPersonRepo(),
            $this->getSentEmailRepo($id, $sentEmail));

        $this->assertSame($sentEmail, $handler->getInitialMessage($id));
    }

    public function testGetValidationMap()
    {
        $person = $this->createMock(SupportPerson::class);
        $person->expects($this->once())->method('getCpf')
            ->willReturn($this->getPersonalData(null, null, null, 'cpf', true));

        $person->expects($this->once())->method('getBirthday')
            ->willReturn($this->getPersonalData(null, null, null, null, false));

        $person->expects($this->once())->method('getEmail')
            ->willReturn($this->getPersonalData('email', 'hash', 'challenge', null, true));

        $person->expects($this->once())->method('getPhoneNumber')
            ->willReturn($this->getPersonalData('phoneNumber', 'hash', 'challenge', null, true));

        $handler = new SupportHandler($this->getAuthChecker(), $this->getPersonRepo(), $this->getSentEmailRepo());

        $map = $handler->getValidationMap($person);

        $this->assertNotEmpty($map);
        $this->assertArrayNotHasKey('birthday', $map);
        $this->assertArrayNotHasKey('cpf', $map);
        // Phone
        $this->assertEquals('challenge', $map['phoneNumber']['challenge']);
        $this->assertEquals('hash', $map['phoneNumber']['hash']);
        // Email
        $this->assertEquals('challenge', $map['email']['challenge']);
        $this->assertEquals('hash', $map['email']['hash']);
    }

    private function getPersonalData(?string $name, ?string $hash, ?string $challenge, $value, bool $filled)
    {
        $data = $this->createMock(PersonalData::class);
        $data->expects($this->once())->method('isValueFilled')->willReturn($filled);
        if (null !== $value) {
            $data->expects($this->once())->method('getValue')->willReturn($value);
        }
        if (null !== $challenge) {
            $data->expects($this->once())->method('getChallenge')->willReturn($challenge);
        }
        if (null !== $name) {
            $data->expects($this->once())->method('getName')->willReturn($name);
        }
        if (null !== $hash) {
            $data->expects($this->once())->method('getHash')->willReturn($hash);
        }

        return $data;
    }

    /**
     * @return MockObject|AuthorizationCheckerInterface
     */
    private function getAuthChecker()
    {
        return $this->createMock(AuthorizationCheckerInterface::class);
    }

    /**
     * @param null $id
     * @param null $person
     * @return MockObject|PersonRepository
     */
    private function getPersonRepo($id = null, $person = null)
    {
        $personRepo = $this->createMock(PersonRepository::class);
        if (null !== $id) {
            $personRepo->expects($this->once())->method('find')->with($id)->willReturn($person);
        }

        return $personRepo;
    }

    /**
     * @param null $id
     * @param null $sentEmail
     * @return MockObject|SentEmailRepository
     */
    private function getSentEmailRepo($id = null, $sentEmail = null)
    {
        $sentEmailRepo = $this->createMock(SentEmailRepository::class);
        if (null !== $id) {
            $sentEmailRepo->expects($this->once())->method('find')->with($id)->willReturn($sentEmail);
        }

        return $sentEmailRepo;
    }
}
