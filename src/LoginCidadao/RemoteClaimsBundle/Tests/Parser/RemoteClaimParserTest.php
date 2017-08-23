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

class RemoteClaimParserTest extends \PHPUnit_Framework_TestCase
{
    private $claimMetadata = [
        'claim_version' => 1,
        'claim_name' => 'tag:example.com,2017:my_claim',
        'claim_display_name' => 'My Claim',
        'claim_description' => 'My very nice claim description.',
        'claim_provider_recommended_scope' => ['scope1', 'scope2', 'scope3'],
        'claim_provider_essential_scope' => ['scope2'],
        'claim_provider' => [
            'client_name' => 'The Provider',
            'redirect_uris' => ['https://redirect.uri'],
            'bar' => 'foo',
        ],
        'foo' => 'bar',
    ];

    public function testParseClaimString()
    {
        $claim = RemoteClaimParser::parseClaim(json_encode($this->claimMetadata), new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseClaimArray()
    {
        $claim = RemoteClaimParser::parseClaim($this->claimMetadata, new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseClaimObject()
    {
        $claim = RemoteClaimParser::parseClaim((object)$this->claimMetadata, new RemoteClaim(), new Client());

        $this->assertClaimAndProvider($claim);
    }

    public function testParseJwt()
    {
        $token = new Token();
        $token->addClaim(new Claim\Audience(['lc_aud']));
        $token->addClaim(new Claim\Expiration(new \DateTime('30 minutes')));
        $token->addClaim(new Claim\IssuedAt(new \DateTime()));
        $token->addClaim(new Claim\Issuer($this->claimMetadata['claim_provider']['redirect_uris'][0]));
        foreach ($this->claimMetadata as $name => $value) {
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
        $this->setExpectedException('\InvalidArgumentException');

        RemoteClaimParser::parseJwt('INVALID JWT', new RemoteClaim(), new Client());
    }

    public function testParseInvalidClaim()
    {
        $this->setExpectedException('\InvalidArgumentException');

        RemoteClaimParser::parseClaim(null, new RemoteClaim());
    }

    private function assertClaimAndProvider(RemoteClaimInterface $claim)
    {
        $this->assertEquals($this->claimMetadata['claim_name'], $claim->getName());
        $this->assertEquals($this->claimMetadata['claim_display_name'], $claim->getDisplayName());
        $this->assertEquals($this->claimMetadata['claim_description'], $claim->getDescription());
        $this->assertEquals($this->claimMetadata['claim_provider_recommended_scope'], $claim->getRecommendedScope());
        $this->assertEquals($this->claimMetadata['claim_provider_essential_scope'], $claim->getEssentialScope());

        $expectedProvider = $this->claimMetadata['claim_provider'];
        $this->assertEquals($expectedProvider['client_name'], $claim->getProvider()->getName());
        $this->assertEquals($expectedProvider['redirect_uris'], $claim->getProvider()->getRedirectUris());
    }
}
