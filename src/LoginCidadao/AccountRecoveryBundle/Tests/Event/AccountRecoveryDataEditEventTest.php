<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Tests\Event;

use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Event\AccountRecoveryDataEditEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AccountRecoveryDataEditEventTest extends TestCase
{
    public function testEvent()
    {
        $data = new AccountRecoveryData();
        $initialResponse = new Response('initial');
        $finalResponse = new Response('final');

        $event = new AccountRecoveryDataEditEvent($data, $initialResponse);

        $this->assertSame($initialResponse, $event->getResponse());

        $event->setResponse($finalResponse);
        $this->assertSame($finalResponse, $event->getResponse());

        $this->assertSame($data, $event->getAccountRecoveryData());
    }
}
