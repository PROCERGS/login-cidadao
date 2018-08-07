<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Parser;

use Emarref\Jwt\Jwt;
use Emarref\Jwt\Token;
use Emarref\Jwt\Claim;
use Emarref\Jwt\Encryption;
use Emarref\Jwt\Algorithm;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Parser\RemoteClaimParser;
use PHPUnit\Framework\TestCase;

class RemoteClaimParserTest extends TestCase
{
    public static $claimMetadata = [
        'claim_version' => 1,
        'claim_name' => 'tag:example.com,2017:my_claim',
        'claim_display_name' => 'My Claim',
        'claim_description' => 'My very nice claim description.',
        'claim_provider_recommended_scope' => ['scope1', 'scope2', 'scope3'],
        'claim_provider_essential_scope' => ['scope2'],
        'claim_provider' => [
            'client_id' => '123_r4nd0mid',
            'client_name' => 'The Provider',
            'redirect_uris' => ['https://redirect.uri'],
            'bar' => 'foo',
        ],
        'foo' => 'bar',
    ];

    public function testParseClaimString()
    {
        $claim = RemoteClaimParser::parseClaim(json_encode(self::$claimMetadata), new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseClaimArray()
    {
        $claim = RemoteClaimParser::parseClaim(self::$claimMetadata, new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseClaimObject()
    {
        $claim = RemoteClaimParser::parseClaim((object)self::$claimMetadata, new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseJwt()
    {
        $token = new Token();
        $token->addClaim(new Claim\Audience(['lc_aud']));
        $token->addClaim(new Claim\Expiration(new \DateTime('30 minutes')));
        $token->addClaim(new Claim\IssuedAt(new \DateTime()));
        $token->addClaim(new Claim\Issuer(self::$claimMetadata['claim_provider']['redirect_uris'][0]));
        foreach (self::$claimMetadata as $name => $value) {
            $token->addClaim(new Claim\PublicClaim($name, $value));
        }

        $secret = 'my_jwt_secret';
        $encryption = Encryption\Factory::create(new Algorithm\Hs256($secret));
        $jwt = (new Jwt())->serialize($token, $encryption);

        $claim = RemoteClaimParser::parseJwt($jwt, new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testInvalidJwt()
    {
        $this->expectException(\InvalidArgumentException::class);

        RemoteClaimParser::parseJwt('INVALID JWT', new RemoteClaim(), new Client());
    }

    public function testParseInvalidClaim()
    {
        $this->expectException(\InvalidArgumentException::class);

        RemoteClaimParser::parseClaim(null, new RemoteClaim());
    }

    public function testParseMinimalRemoteClaim()
    {
        $provider = [
            'client_id' => self::$claimMetadata['claim_provider']['client_id'],
            'client_name' => self::$claimMetadata['claim_provider']['client_name'],
        ];

        $string = json_encode([
            'claim_schema_version' => '1',
            'claim_name' => self::$claimMetadata['claim_name'],
            'claim_display_name' => self::$claimMetadata['claim_display_name'],
            'claim_provider' => $provider,
        ]);

        $claim = RemoteClaimParser::parseClaim($string, new RemoteClaim(), new Client());
        $this->assertEquals(self::$claimMetadata['claim_name'], $claim->getName());
        $this->assertEquals(self::$claimMetadata['claim_display_name'], $claim->getDisplayName());
        $this->assertNull($claim->getDescription());
        $this->assertEmpty($claim->getRecommendedScope());
        $this->assertEmpty($claim->getEssentialScope());

        $expectedProvider = self::$claimMetadata['claim_provider'];
        $this->assertEquals($expectedProvider['client_id'], $claim->getProvider()->getClientId());
        $this->assertEquals($expectedProvider['client_name'], $claim->getProvider()->getName());
        $this->assertEmpty($claim->getProvider()->getRedirectUris());
    }

    private function assertClaimAndProvider(RemoteClaimInterface $claim)
    {
        $this->assertEquals(self::$claimMetadata['claim_name'], $claim->getName());
        $this->assertEquals(self::$claimMetadata['claim_display_name'], $claim->getDisplayName());
        $this->assertEquals(self::$claimMetadata['claim_description'], $claim->getDescription());
        $this->assertEquals(self::$claimMetadata['claim_provider_recommended_scope'], $claim->getRecommendedScope());
        $this->assertEquals(self::$claimMetadata['claim_provider_essential_scope'], $claim->getEssentialScope());

        $expectedProvider = self::$claimMetadata['claim_provider'];
        $this->assertEquals($expectedProvider['client_id'], $claim->getProvider()->getClientId());
        $this->assertEquals($expectedProvider['client_name'], $claim->getProvider()->getName());
        $this->assertEquals($expectedProvider['redirect_uris'], $claim->getProvider()->getRedirectUris());
    }
}
