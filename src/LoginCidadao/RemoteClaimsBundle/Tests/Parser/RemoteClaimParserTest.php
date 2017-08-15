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

use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
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

    private function assertClaimAndProvider($claim)
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
