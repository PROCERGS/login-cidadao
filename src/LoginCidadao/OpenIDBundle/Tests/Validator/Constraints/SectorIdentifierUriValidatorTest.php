<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Validator\Constraints;

use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OAuthBundle\Entity\OrganizationRepository;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Validator\Constraints\SectorIdentifierUri;
use LoginCidadao\OpenIDBundle\Validator\Constraints\SectorIdentifierUriValidator;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use PHPUnit\Framework\TestCase;

class SectorIdentifierUriValidatorTest extends TestCase
{
    public function testValidate()
    {
        $uri = 'https://example.com';
        $metadata = (new ClientMetadata())
            ->setSectorIdentifierUri($uri);
        $constraint = new SectorIdentifierUri();
        $organization = new Organization();

        /** @var \PHPUnit_Framework_MockObject_MockObject|OrganizationRepository $repo */
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\OrganizationRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['sectorIdentifierUri' => $uri])
            ->willReturn($organization);

        /** @var SectorIdentifierUriChecker|\PHPUnit_Framework_MockObject_MockObject $uriChecker */
        $uriChecker = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker')
            ->disableOriginalConstructor()->getMock();
        $uriChecker->expects($this->once())->method('check')->with($metadata, $uri)->willReturn(true);

        $validator = new SectorIdentifierUriValidator($repo, $uriChecker);
        $validator->validate($metadata, $constraint);

        $this->assertSame($organization, $metadata->getOrganization());
    }

    public function testNoUri()
    {
        $metadata = new ClientMetadata();
        $constraint = new SectorIdentifierUri();

        /** @var OrganizationRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\OrganizationRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('findOneBy');

        /** @var SectorIdentifierUriChecker|\PHPUnit_Framework_MockObject_MockObject $uriChecker */
        $uriChecker = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker')
            ->disableOriginalConstructor()->getMock();
        $uriChecker->expects($this->never())->method('check');

        $validator = new SectorIdentifierUriValidator($repo, $uriChecker);
        $validator->validate($metadata, $constraint);

        $this->assertNull($metadata->getOrganization());
    }

    public function testCheckFailed()
    {
        $uri = 'https://example.com';
        $metadata = (new ClientMetadata())
            ->setSectorIdentifierUri($uri);
        $constraint = new SectorIdentifierUri();
        $organization = new Organization();

        /** @var OrganizationRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\OrganizationRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['sectorIdentifierUri' => $uri])
            ->willReturn($organization);

        /** @var SectorIdentifierUriChecker|\PHPUnit_Framework_MockObject_MockObject $uriChecker */
        $uriChecker = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker')
            ->disableOriginalConstructor()->getMock();
        $uriChecker->expects($this->once())->method('check')->with($metadata, $uri)->willReturn(false);

        $validator = new SectorIdentifierUriValidator($repo, $uriChecker);
        $validator->validate($metadata, $constraint);

        $this->assertNull($metadata->getOrganization());
    }
}
