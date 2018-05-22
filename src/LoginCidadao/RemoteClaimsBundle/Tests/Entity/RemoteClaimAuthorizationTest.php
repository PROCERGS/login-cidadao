<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Entity;

use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class RemoteClaimAuthorizationTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoteClaimAuthorization()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface $client */
        $client = $this->getMock('LoginCidadao\OAuthBundle\Model\ClientInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|ClaimProviderInterface $provider */
        $provider = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|PersonInterface $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $claimName = new TagUri();
        $accessToken = 'my_accessToken';

        $auth = (new RemoteClaimAuthorization())
            ->setClaimProvider($provider)
            ->setClient($client)
            ->setClaimName($claimName)
            ->setAccessToken($accessToken)
            ->setPerson($person);

        $this->assertEquals($client, $auth->getClient());
        $this->assertEquals($provider, $auth->getClaimProvider());
        $this->assertEquals($claimName, $auth->getClaimName());
        $this->assertEquals($accessToken, $auth->getAccessToken());
        $this->assertEquals($person, $auth->getPerson());
    }
}
