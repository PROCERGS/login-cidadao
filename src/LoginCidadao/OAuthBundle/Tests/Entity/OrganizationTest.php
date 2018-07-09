<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\Entity;

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\Organization;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    public function testOrganization()
    {
        $organization = (new Organization())
            ->setId($id = 'orgId')
            ->setName($name = 'My Organization')
            ->setMembers($members = [new Person()])
            ->setVerifiedAt($verifiedAt = new \DateTime())
            ->setDomain($domain = 'example.com')
            ->setClients($clients = [new Client()])
            ->setValidationUrl($validationUri = 'https://example.com/validate')
            ->setValidationSecret($validationSecret = 'val-secret')
            ->setValidatedUrl($validatedUri = 'https://example.com/validated')
            ->setTrusted(true)
            ->setSectorIdentifierUri($sectorUri = 'https://example.com');

        $this->assertSame($id, $organization->getId());
        $this->assertSame($name, $organization->getName());
        $this->assertSame($members, $organization->getMembers());
        $this->assertSame($verifiedAt, $organization->getVerifiedAt());
        $this->assertSame($domain, $organization->getDomain());
        $this->assertSame($clients, $organization->getClients());
        $this->assertSame($validationUri, $organization->getValidationUrl());
        $this->assertSame($validationSecret, $organization->getValidationSecret());
        $this->assertSame($sectorUri, $organization->getSectorIdentifierUri());

        $this->assertTrue($organization->isTrusted());
        $this->assertTrue($organization->isVerified());

        $this->assertFalse($organization->checkValidation());
        $this->assertNull($organization->getVerifiedAt());
        $this->assertFalse($organization->isVerified());

        $organization->setValidatedUrl($organization->getValidationUrl());
        $organization->setVerifiedAt(new \DateTime());
        $this->assertTrue($organization->checkValidation());
        $this->assertSame($name, $organization->__toString());
    }
}
