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

use PROCERGS\LoginCidadao\NfgBundle\Event\GetLoginCallbackResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class GetLoginCallbackResponseEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $params = ['some' => 'param'];
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $event = new GetLoginCallbackResponseEvent($params, $response);

        $this->assertEquals($params, $event->getParams());
        $this->assertEquals($response, $event->getResponse());

        $newResponse = new Response('content');
        $event->setResponse($newResponse);

        $this->assertEquals($newResponse, $event->getResponse());
    }
}
