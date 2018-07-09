<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Event;

use LoginCidadao\RemoteClaimsBundle\Event\UpdateRemoteClaimUriEvent;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use PHPUnit\Framework\TestCase;

class UpdateRemoteClaimUriEventTest extends TestCase
{
    public function testEvent()
    {
        $claimName = new TagUri();
        $uri = 'https://dummy.tld/dummy';

        $event = new UpdateRemoteClaimUriEvent($claimName, $uri);
        $this->assertSame($claimName, $event->getClaimName());
        $this->assertSame($uri, $event->getNewUri());
    }
}
