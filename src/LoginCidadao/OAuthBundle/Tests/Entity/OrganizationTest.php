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

use LoginCidadao\OAuthBundle\Entity\Organization;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    public function testOrganization()
    {
        $id = '321';
        $name = 'Organization Name';
        $members = [$this->getPerson()];
        $verifiedAt = new \DateTime();
        $domain = 'domain.com';
        $clients = [$this->getClient()];
        $validationUrl = 'https://validation.url';
        $secret = 'super secret';
        $sectorIdentifierUri = 'https://sector.identifier';

        $organization = new Organization();
        $this->assertFalse($organization->isVerified());
        $organization
            ->setId($id)
            ->setName($name)
            ->setMembers($members)
            ->setVerifiedAt($verifiedAt)
            ->setDomain($domain)
            ->setClients($clients)
            ->setValidationUrl($validationUrl)
            ->setValidationSecret($secret)
            ->setTrusted(true);

        $organization->setSectorIdentifierUri($sectorIdentifierUri);

        $this->assertEquals($id, $organization->getId());
        $this->assertEquals($name, $organization->getName());
        $this->assertEquals($name, $organization->__toString());
        $this->assertEquals($members, $organization->getMembers());
        $this->assertEquals($verifiedAt, $organization->getVerifiedAt());
        $this->assertEquals($domain, $organization->getDomain());
        $this->assertEquals($clients, $organization->getClients());
        $this->assertEquals($validationUrl, $organization->getValidationUrl());
        $this->assertEquals($secret, $organization->getValidationSecret());
        $this->assertEquals($sectorIdentifierUri, $organization->getSectorIdentifierUri());
        $this->assertTrue($organization->isTrusted());
        $this->assertTrue($organization->isVerified());
    }

    public function testCheckValidation()
    {
        $validationUrl = 'https://validation.url';
        $validatedUrl = 'https://validated.url';
        $organization = new Organization();
        $organization->setValidatedUrl($validatedUrl);
        $organization->setValidationUrl($validationUrl);

        $this->assertFalse($organization->checkValidation());

        $organization->setValidatedUrl($validationUrl);
        $this->assertTrue($organization->checkValidation());
    }

    private function getPerson()
    {
        return $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    private function getClient()
    {
        return $this->getMock('LoginCidadao\OAuthBundle\Model\ClientInterface');
    }
}
