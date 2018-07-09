<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\NfgBundle\Event\GetDisconnectCallbackResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class GetDisconnectCallbackResponseEventTest extends TestCase
{
    public function testEvent()
    {
        $personMeuRS = $this->createMock('PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS');
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');

        $event = new GetDisconnectCallbackResponseEvent($personMeuRS, $response);

        $this->assertEquals($personMeuRS, $event->getPersonMeuRS());
        $this->assertEquals($response, $event->getResponse());

        $newResponse = new Response('content');
        $event->setResponse($newResponse);

        $this->assertEquals($newResponse, $event->getResponse());
    }
}
