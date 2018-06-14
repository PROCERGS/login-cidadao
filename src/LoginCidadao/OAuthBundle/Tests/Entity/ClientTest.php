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

use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\OAuthBundle\Entity\Client;
use OAuth2\OAuth2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class ClientTest extends TestCase
{
    public function testGetAllGrants()
    {
        $this->assertCount(6, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_AUTH_CODE, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_IMPLICIT, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_USER_CREDENTIALS, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_REFRESH_TOKEN, Client::getAllGrants());
        $this->assertContains(OAuth2::GRANT_TYPE_EXTENSIONS, Client::getAllGrants());
    }

    public function testClientWithoutMetadata()
    {
        $id = 123;
        $randomId = 'randomId';
        $secret = 'mySuperSecret Key Here!';
        $name = 'Client Name';
        $description = 'Some Client description here...';
        $siteUrl = 'https://site.url';
        $landingPageUrl = 'https://landing.page.url';
        $tosUrl = 'https://tos.url';
        $allowedScope = ['scope1', 'scope2'];
        $owners = [$this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface')];
        $uid = 'client-uid';
        $redirectUris = ['https://redirect.uri'];
        /** @var UploadedFile $image */
        $image = $this->createMock('Psr\Http\Message\UploadedFileInterface');
        $imageName = 'image.name';

        $client = new Client();

        $this->assertContains('public_profile', $client->getAllowedScopes());
        $this->assertContains('openid', $client->getAllowedScopes());

        $client
            ->setId($id)
            ->setName($name)
            ->setDescription($description)
            ->setSiteUrl($siteUrl)
            ->setLandingPageUrl($landingPageUrl)
            ->setTermsOfUseUrl($tosUrl)
            ->setAllowedScopes($allowedScope)
            ->setVisible(true)
            ->setPublished(true)
            ->setOwners($owners)
            ->setUid($uid)
            ->setRedirectUris($redirectUris)
            ->setImage($image)
            ->setImageName($imageName)
            ->setUpdatedAt();
        $client->setRandomId($randomId);
        $client->setSecret($secret);

        $this->assertEquals($id, $client->getId());
        $this->assertEquals($name, $client->getName());
        $this->assertEquals($description, $client->getDescription());
        $this->assertEquals($siteUrl, $client->getSiteUrl());
        $this->assertEquals($landingPageUrl, $client->getLandingPageUrl());
        $this->assertEquals($tosUrl, $client->getTermsOfUseUrl());
        $this->assertEquals($allowedScope, $client->getAllowedScopes());
        $this->assertTrue($client->isVisible());
        $this->assertTrue($client->isPublished());
        $this->assertContains(reset($owners), $client->getOwners());
        $this->assertEquals($uid, $client->getUid());
        $this->assertEquals($redirectUris, $client->getRedirectUris());
        $this->assertEquals($image, $client->getImage());
        $this->assertEquals($imageName, $client->getImageName());
        $this->assertEquals($secret, $client->getSecret());
        $this->assertEquals($secret, $client->getClientSecret());
        $this->assertEquals("{$id}_{$randomId}", $client->getPublicId());
        $this->assertEquals("{$id}_{$randomId}", $client->getClientId());

        $autoDate = $client->getUpdatedAt();
        $this->assertInstanceOf('\DateTime', $autoDate);

        $date = new \DateTime();
        $client->setUpdatedAt($date);
        $this->assertNotSame($autoDate, $date);
        $this->assertSame($date, $client->getUpdatedAt());

        return $client;
    }

    public function testUpdateMetadata()
    {
        // Client data
        $name = 'Client Name';
        $siteUrl = 'https://site.url';
        $landingPageUrl = 'https://landing.page.url';
        $tosUrl = 'https://tos.url';
        $redirectUris = ['https://redirect.uri'];
        $grantTypes = [OAuth2::GRANT_TYPE_AUTH_CODE];

        // ClientMetadata
        $metadataName = 'ClientMetadata Name';
        $metadataSiteUrl = 'https://metadata.site.url';
        $metadataLandingPageUrl = 'https://metadata.landing.page.url';
        $metadataToSUrl = 'http://metadata.tos.url';
        $metadataRedirectUris = ['https://metadata.redirect.uri'];
        $metadataGrantTypes = [OAuth2::GRANT_TYPE_IMPLICIT];

        $client = new Client();
        $client
            ->setName($name)
            ->setSiteUrl($siteUrl)
            ->setLandingPageUrl($landingPageUrl)
            ->setTermsOfUseUrl($tosUrl)
            ->setRedirectUris($redirectUris)
            ->setAllowedGrantTypes($grantTypes);

        $metadata = new ClientMetadata();
        $metadata
            ->setClientName($metadataName)
            ->setClientUri($metadataSiteUrl)
            ->setInitiateLoginUri($metadataLandingPageUrl)
            ->setTosUri($metadataToSUrl)
            ->setRedirectUris($metadataRedirectUris)
            ->setGrantTypes($metadataGrantTypes);

        $this->assertNotEquals($metadataName, $client->getName());
        $this->assertNotEquals($metadataSiteUrl, $client->getSiteUrl());
        $this->assertNotEquals($metadataLandingPageUrl, $client->getLandingPageUrl());
        $this->assertNotEquals($metadataToSUrl, $client->getTermsOfUseUrl());
        $this->assertNotEquals($metadataRedirectUris, $client->getRedirectUris());
        $this->assertNotEquals($metadataGrantTypes, $client->getAllowedGrantTypes());

        $client->setMetadata($metadata);

        $this->assertEquals($metadataName, $client->getName());
        $this->assertEquals($metadataSiteUrl, $client->getSiteUrl());
        $this->assertEquals($metadataLandingPageUrl, $client->getLandingPageUrl());
        $this->assertEquals($metadataToSUrl, $client->getTermsOfUseUrl());
        $this->assertEquals($metadataRedirectUris, $client->getRedirectUris());
        $this->assertEquals($metadataGrantTypes, $client->getAllowedGrantTypes());

        $changedName = 'Changed Name';
        $changedUrl = 'https://changed.url';
        $client->setName($changedName);
        $client->setSiteUrl($changedUrl);
        $client->setLandingPageUrl($changedUrl);
        $client->setTermsOfUseUrl($changedUrl);
        $client->setRedirectUris([$changedUrl]);

        $this->assertEquals($changedName, $metadata->getClientName());
        $this->assertEquals($changedUrl, $metadata->getClientUri());
        $this->assertEquals($changedUrl, $metadata->getInitiateLoginUri());
        $this->assertEquals($changedUrl, $metadata->getTosUri());
        $this->assertContains($changedUrl, $metadata->getRedirectUris());
    }

    public function testOwnsDomain()
    {
        $client = new Client();
        $client->setRedirectUris(['https://domain1.com/', 'https://domain2.com']);

        $this->assertTrue($client->ownsDomain('domain1.com'));
        $this->assertTrue($client->ownsDomain('domain2.com'));
        $this->assertFalse($client->ownsDomain('domain3.com'));
        $this->assertFalse($client->ownsDomain('sub.domain2.com'));
    }

    public function testGetContacts()
    {
        $person1Email = 'person1@email.com';
        $person2Email = 'person2@email.com';
        $person1 = $this->getPerson();
        $person1->expects($this->once())
            ->method('getEmail')->willReturn($person1Email);

        $person2 = $this->getPerson();
        $person2->expects($this->once())
            ->method('getEmail')->willReturn($person2Email);

        $client = new Client();
        $client->setOwners([$person1, $person2]);

        $contacts = $client->getContacts();
        $this->assertContains($person1Email, $contacts);
        $this->assertContains($person2Email, $contacts);

        $metadata = new ClientMetadata();
        $client->setMetadata($metadata);
        $metadata->setContacts(['person1@email.com', 'person2@email.com']);

        $contacts = $client->getContacts();
        $this->assertContains($person1Email, $contacts);
        $this->assertContains($person2Email, $contacts);
    }

    public function testRemoveAuthorizationAsArray()
    {
        /** @var MockObject|Authorization $authorization */
        $authorization = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');
        $authorization->expects($this->any())
            ->method('getId')->willReturn('1');

        $client = new Client();
        $this->assertEmpty($client->getAuthorizations());

        $client->setAuthorizations([$authorization]);
        $this->assertContains($authorization, $client->getAuthorizations());

        $client->removeAuthorization($authorization);
        $this->assertEmpty($client->getAuthorizations());
    }

    public function testRemoveAuthorizationAsArrayCollection()
    {
        /** @var MockObject|Authorization $authorization */
        $authorization = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');
        $authorization->expects($this->any())
            ->method('getId')->willReturn('1');

        $client = new Client();
        $this->assertEmpty($client->getAuthorizations());

        $client->setAuthorizations(new ArrayCollection([$authorization]));
        $this->assertContains($authorization, $client->getAuthorizations());

        $client->removeAuthorization($authorization);
        $this->assertEmpty($client->getAuthorizations());
    }

    public function testGetName()
    {
        $name = 'Client Name';

        $metadata = new ClientMetadata();

        $client = new Client();
        $client->setName($name);
        $client->setMetadata($metadata);

        $this->assertNull($metadata->getClientName());

        $client->getName();
        $this->assertEquals($name, $metadata->getClientName());
    }

    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }
}
