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

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use PHPUnit\Framework\TestCase;

class ClientMetadataTest extends TestCase
{
    public function testEntity()
    {
        $client = new Client();
        $client->getOwners()->add((new Person())->setEmail('email@example.com'));

        /** @var ClientMetadata $metadata */
        $metadata = (new ClientMetadata())
            ->setId($id = 'my id')
            ->setResponseTypes(null)
            ->setApplicationType(null)
            ->setGrantTypes(null)
            ->setSubjectType(null)
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
            ->setPostLogoutRedirectUris($uris)
            ->setRequestUris($uris);
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
        $this->assertSame('ALG', $metadata->getIdTokenEncryptedResponseAlg());
        $this->assertSame('ENC', $metadata->getIdTokenEncryptedResponseEnc());
        $this->assertSame('ALG', $metadata->getUserinfoSignedResponseAlg());
        $this->assertSame('ALG', $metadata->getUserinfoEncryptedResponseAlg());
        $this->assertSame('ENC', $metadata->getUserinfoEncryptedResponseEnc());
        $this->assertSame('ALG', $metadata->getRequestObjectSigningAlg());
        $this->assertSame('ALG', $metadata->getRequestObjectEncryptionAlg());
        $this->assertSame('ENC', $metadata->getRequestObjectEncryptionEnc());
        $this->assertSame('ALG', $metadata->getTokenEndpointAuthSigningAlg());
        $this->assertEmpty($metadata->getDefaultAcrValues());
        $this->assertSame($uris, $metadata->getRequestUris());
    }

    public function testSmarterMethods()
    {
        $client = new Client();
        $client->setId(123);
        $client->setRandomId('random');
        $client->setSecret('my little secret');
        $metadata = (new ClientMetadata())
            ->setClient($client)
            ->setRedirectUris(['https://example.com']);

        $this->assertSame($client->getClientId(), $metadata->getClientId());
        $this->assertSame($client->getClientSecret(), $metadata->getClientSecret());
        $this->assertSame('example.com', $metadata->getSectorIdentifier());
    }

    public function testCreateFromClient()
    {
        $client = new Client();
        $client->setAllowedScopes(['grant1']);
        $client->setSiteUrl('https://example.com');
        $client->setTermsOfUseUrl('https://example.com');
        $client->setName('My Client');
        $client->setRedirectUris(['https://example.com']);
        $client->setId('123');
        $client->setRandomId('random_part');
        $client->setSecret('my very secret key');

        $metadata = (new ClientMetadata())
            ->fromClient($client);

        $this->assertSame($client->getAllowedGrantTypes(), $metadata->getGrantTypes());
        $this->assertSame($client->getSiteUrl(), $metadata->getClientUri());
        $this->assertSame($client->getTermsOfUseUrl(), $metadata->getTosUri());
        $this->assertSame($client->getName(), $metadata->getClientName());
        $this->assertSame($client->getRedirectUris(), $metadata->getRedirectUris());
        $this->assertSame($client->getPublicId(), $metadata->getClientId());
        $this->assertSame($client->getSecret(), $metadata->getClientSecret());
    }

    public function testToClient()
    {
        $uri = 'https://example.com';

        $metadata = (new ClientMetadata())
            ->setClientUri($uri)
            ->setTosUri($uri)
            ->setClientName('My Client')
            ->setRedirectUris([$uri]);
        $metadata->checkDefaults();

        $client = $metadata->toClient();
        $this->assertSame($metadata->getGrantTypes(), $client->getAllowedGrantTypes());
        $this->assertSame($metadata->getClientUri(), $client->getLandingPageUrl());
        $this->assertSame($metadata->getClientUri(), $client->getSiteUrl());
        $this->assertSame($metadata->getTosUri(), $client->getTermsOfUseUrl());
        $this->assertSame('My Client', $client->getName());
        $this->assertSame($metadata->getRedirectUris(), $client->getRedirectUris());
        $this->assertFalse($client->isVisible());
        $this->assertFalse($client->isPublished());
    }

    public function testUriCanonicalizer()
    {
        $uri1 = 'https://example.com';
        $uri2 = 'https://example.com/';

        $this->assertSame('https://example.com/', ClientMetadata::canonicalizeUri($uri1));
        $this->assertSame('https://example.com/', ClientMetadata::canonicalizeUri($uri2));
    }
}
