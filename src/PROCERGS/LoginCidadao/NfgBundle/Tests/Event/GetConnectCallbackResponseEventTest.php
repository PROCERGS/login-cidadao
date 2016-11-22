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

use PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class GetConnectCallbackResponseEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $personMeuRS = $this->getMock('PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS');
        $overrideExisting = true;
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $event = new GetConnectCallbackResponseEvent($request, $personMeuRS, $overrideExisting, $response);

        $this->assertEquals($request, $event->getRequest());
        $this->assertEquals($personMeuRS, $event->getPersonMeuRS());
        $this->assertEquals($overrideExisting, $event->isOverrideExisting());
        $this->assertEquals($response, $event->getResponse());

        $newResponse = new Response('content');
        $event->setResponse($newResponse);

        $this->assertEquals($newResponse, $event->getResponse());
    }
}
