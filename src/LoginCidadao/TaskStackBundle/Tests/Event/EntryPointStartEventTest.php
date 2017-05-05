<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Tests\Event;

use LoginCidadao\TaskStackBundle\Event\EntryPointStartEvent;

class EntryPointStartEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $authException = $this->getMock('Symfony\Component\Security\Core\Exception\AuthenticationException');
        $event = new EntryPointStartEvent($request, $authException);

        $this->assertEquals($request, $event->getRequest());
        $this->assertEquals($authException, $event->getAuthenticationException());
    }
}
