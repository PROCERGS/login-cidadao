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

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Entity\SentEmailRepository;
use LoginCidadao\CoreBundle\Model\IdentifiablePersonInterface;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
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

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $this->getPersonRepo($id, $person),
            $this->getSentEmailRepo()
        );
        $supportPerson = $handler->getSupportPerson($id);

        $this->assertInstanceOf(SupportPerson::class, $supportPerson);
    }

    public function testGetNonExistingSupportPerson()
    {
        $this->expectException(PersonNotFoundException::class);
        $id = 123;

        $handler = new SupportHandler($this->getAuthChecker(), $this->getPhoneVerificationService(),
            $this->getPersonRepo($id, null),
            $this->getSentEmailRepo());
        $handler->getSupportPerson($id);
    }

    public function testGetInitialMessage()
    {
        $id = 123;
        $sentEmail = $this->createMock(SentEmail::class);

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $this->getPersonRepo(),
            $this->getSentEmailRepo($id, $sentEmail)
        );

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

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $this->getPersonRepo(),
            $this->getSentEmailRepo()
        );

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

    public function testGetPhoneMetadata()
    {
        $id = 321;
        $phoneNumber = new PhoneNumber();

        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        $supportPerson = $this->createMock(SupportPerson::class);
        $supportPerson->expects($this->once())->method('getId')->willReturn($id);

        $personRepo = $this->getPersonRepo($id, $person);
        $personRepo->expects($this->once())->method('countByPhone')->with($phoneNumber)->willReturn(3);

        $verification = $this->createMock(PhoneVerificationInterface::class);
        $verificationService = $this->getPhoneVerificationService();
        $verificationService->expects($this->once())
            ->method('getPhoneVerification')->with($person, $phoneNumber)->willReturn($verification);

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $verificationService,
            $personRepo,
            $this->getSentEmailRepo()
        );
        $metadata = $handler->getPhoneMetadata($supportPerson);

        $this->assertEquals(3, $metadata['samePhoneCount']);
        $this->assertSame($verification, $metadata['verification']);
    }

    public function testGetPhoneMetadataNoVerification()
    {
        $id = 321;
        $phoneNumber = new PhoneNumber();

        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        $supportPerson = $this->createMock(SupportPerson::class);
        $supportPerson->expects($this->once())->method('getId')->willReturn($id);

        $personRepo = $this->getPersonRepo($id, $person);
        $personRepo->expects($this->once())->method('countByPhone')->with($phoneNumber)->willReturn(3);

        $verificationService = $this->getPhoneVerificationService();
        $verificationService->expects($this->once())
            ->method('getPhoneVerification')->with($person, $phoneNumber)->willReturn(null);

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $verificationService,
            $personRepo,
            $this->getSentEmailRepo()
        );
        $metadata = $handler->getPhoneMetadata($supportPerson);

        $this->assertEquals(3, $metadata['samePhoneCount']);
        $this->assertNull($metadata['verification']);
    }

    public function testGetPhoneMetadataNoPhone()
    {
        $id = 321;

        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getMobile')->willReturn(null);

        $supportPerson = $this->createMock(SupportPerson::class);
        $supportPerson->expects($this->once())->method('getId')->willReturn($id);

        $personRepo = $this->getPersonRepo($id, $person);
        $personRepo->expects($this->never())->method('countByPhone');

        $verificationService = $this->getPhoneVerificationService();
        $verificationService->expects($this->never())->method('getPhoneVerification');

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $verificationService,
            $personRepo,
            $this->getSentEmailRepo()
        );
        $metadata = $handler->getPhoneMetadata($supportPerson);

        $this->assertEquals(0, $metadata['samePhoneCount']);
        $this->assertNull($metadata['verification']);
    }

    public function testThirdPartyConnections()
    {
        /** @var IdentifiablePersonInterface|MockObject $identifiablePerson */
        $identifiablePerson = $this->createMock(IdentifiablePersonInterface::class);
        $identifiablePerson->expects($this->once())->method('getId')->willReturn($id = 666);

        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getFacebookId')->willReturn('facebook');
        $person->expects($this->once())->method('getGoogleId')->willReturn('google');
        $person->expects($this->once())->method('getTwitterId')->willReturn('twitter');

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $this->getPersonRepo($id, $person),
            $this->getSentEmailRepo()
        );
        $this->assertEquals([
            'google' => true,
            'facebook' => true,
            'twitter' => true,
        ], $handler->getThirdPartyConnections($identifiablePerson));
    }

    public function testThirdPartyConnectionsWithPersonInterface()
    {
        $person = $this->createMock(Person::class);
        $person->expects($this->once())->method('getFacebookId')->willReturn('facebook');
        $person->expects($this->once())->method('getGoogleId')->willReturn('google');
        $person->expects($this->once())->method('getTwitterId')->willReturn('twitter');

        $personRepo = $this->getPersonRepo();
        $personRepo->expects($this->never())->method('find');

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $personRepo,
            $this->getSentEmailRepo()
        );
        $this->assertEquals([
            'google' => true,
            'facebook' => true,
            'twitter' => true,
        ], $handler->getThirdPartyConnections($person));
    }

    public function testThirdPartyConnectionsUserNotFound()
    {
        /** @var IdentifiablePersonInterface|MockObject $identifiablePerson */
        $identifiablePerson = $this->createMock(IdentifiablePersonInterface::class);
        $identifiablePerson->expects($this->once())->method('getId')->willReturn($id = 666);

        $handler = new SupportHandler(
            $this->getAuthChecker(),
            $this->getPhoneVerificationService(),
            $this->getPersonRepo($id, null),
            $this->getSentEmailRepo()
        );
        $this->assertEmpty($handler->getThirdPartyConnections($identifiablePerson));
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
            $sentEmailRepo->expects($this->once())->method('findOneBy')
                ->with(['supportTicket' => $id])->willReturn($sentEmail);
        }

        return $sentEmailRepo;
    }

    /**
     * @return MockObject|PhoneVerificationServiceInterface
     */
    private function getPhoneVerificationService()
    {
        return $this->createMock(PhoneVerificationServiceInterface::class);
    }
}
