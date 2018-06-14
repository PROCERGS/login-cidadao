<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\ResponseType;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\ResponseType\IdToken;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use OAuth2\Encryption\EncryptionInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\Storage\PublicKeyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdTokenTest extends TestCase
{
    public function testCreateIdToken()
    {
        $sub = 'abc987';
        $userId = '123456789';
        $client = (new Client())
            ->setId($clientId = '123_abc')
            ->setMetadata($metadata = new ClientMetadata());

        $privateKey = 'dummy';
        $encAlg = 'dummy';

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock(PersonInterface::class);

        /** @var MockObject|UserClaimsInterface $userClaimsStorage */
        $userClaimsStorage = $this->createMock(UserClaimsInterface::class);

        /** @var PublicKeyInterface|MockObject $pubKeyStorage */
        $pubKeyStorage = $this->createMock(PublicKeyInterface::class);
        $pubKeyStorage->expects($this->once())->method('getPrivateKey')->willReturn($privateKey);
        $pubKeyStorage->expects($this->once())->method('getEncryptionAlgorithm')->willReturn($encAlg);

        /** @var EncryptionInterface|MockObject $encryptionUtil */
        $encryptionUtil = $this->createMock(EncryptionInterface::class);
        $encryptionUtil->expects($this->once())
            ->method('encode')->with($this->isType('array'), $privateKey, $encAlg)
            ->willReturnCallback(function ($idToken) use ($sub) {
                $this->assertSame($idToken['sub'], $sub);
            });

        /** @var ClientManager|MockObject $clientManager */
        $clientManager = $this->createMock(ClientManager::class);
        $clientManager->expects($this->once())->method('getClientById')->with($clientId)->willReturn($client);

        /** @var SubjectIdentifierService|MockObject $subIdService */
        $subIdService = $this->createMock(SubjectIdentifierService::class);
        $subIdService->expects($this->once())
            ->method('getSubjectIdentifier')->with($person, $metadata)
            ->willReturn($sub);

        /** @var UserManager|MockObject $userManager */
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())->method('findUserBy')->with(['id' => $userId])->willReturn($person);

        $idToken = new IdToken($userClaimsStorage, $pubKeyStorage, ['issuer' => 'Some IdP'], $encryptionUtil);
        $idToken->setClientManager($clientManager);
        $idToken->setUserManager($userManager);
        $idToken->setSubjectIdentifierService($subIdService);
        $idToken->createIdToken($clientId, $userId);
    }
}
