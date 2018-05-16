<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class ClientMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $client = new Client();
        $client->getOwners()->add((new Person())->setEmail('email@example.com'));

        /** @var ClientMetadata $metadata */
        $metadata = (new ClientMetadata())
            ->setId($id = 'my id')
            ->setClientId($clientId = 'the.client.id')
            ->setClientSecret($clientSecret = 'super secret')
            ->setClient($client)
            ->setRedirectUris($uris = ['https://example.com/'])
            ->setContacts($contacts = ['email@example.com'])
            ->setLogoUri($logoUri = 'https://example.com/mylogo.png')
            ->setClientUri($clientUri = 'https://example.com')
            ->setPolicyUri($policyUri = 'https://example.com/policy')
            ->setTosUri($tosUri = 'https://example.com/tos')
            ->setJwksUri($jwksUri = 'https://example.com/jwks')
            ->setJwks($jwks = 'something')
            ->setSectorIdentifierUri($sectorIdentifierUri = 'https://example.com/sector')
            ->setIdTokenEncryptedResponseAlg('ALG')
            ->setIdTokenEncryptedResponseEnc('ENC')
            ->setUserinfoSignedResponseAlg('ALG')
            ->setUserinfoEncryptedResponseAlg('ALG')
            ->setUserinfoEncryptedResponseEnc('ENC')
            ->setRequestObjectSigningAlg('ALG')
            ->setRequestObjectEncryptionAlg('ALG')
            ->setRequestObjectEncryptionEnc('ENC')
            ->setTokenEndpointAuthSigningAlg('ALG')
            ->setDefaultMaxAge(12345)
            ->setDefaultAcrValues([])
            ->setInitiateLoginUri($loginUri = 'https://example.com/login')
            ->setRegistrationAccessToken($regAccessToken = 'accessToken')
            ->setPostLogoutRedirectUris($uris);
        $metadata->checkDefaults();

        $this->assertSame($clientId, $metadata->getClientId());
        $this->assertSame($clientSecret, $metadata->getClientSecret());
        $this->assertSame($client, $metadata->getClient());
        $this->assertSame($uris, $metadata->getRedirectUris());
        $this->assertContains('email@example.com', $metadata->getContacts());
        $this->assertSame($logoUri, $metadata->getLogoUri());
        $this->assertSame($clientUri, $metadata->getClientUri());
        $this->assertSame($policyUri, $metadata->getPolicyUri());
        $this->assertSame($tosUri, $metadata->getTosUri());
        $this->assertSame($jwksUri, $metadata->getJwksUri());
        $this->assertSame($jwks, $metadata->getJwks());
        $this->assertSame($sectorIdentifierUri, $metadata->getSectorIdentifierUri());
        $this->assertSame(12345, $metadata->getDefaultMaxAge());
        $this->assertSame($loginUri, $metadata->getInitiateLoginUri());
        $this->assertSame($regAccessToken, $metadata->getRegistrationAccessToken());
        $this->assertSame($uris, $metadata->getPostLogoutRedirectUris());
        $this->assertSame('pairwise', $metadata->getSubjectType());
        $this->assertSame('example.com', $metadata->getSectorIdentifier());
    }
}
