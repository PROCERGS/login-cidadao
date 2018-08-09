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
use LoginCidadao\AccountRecoveryBundle\Event\SendResetPasswordSmsEvent;
use PHPUnit\Framework\TestCase;

class SendResetPasswordSmsEventTest extends TestCase
{
    public function testEvent()
    {
        $data = $this->createMock(AccountRecoveryData::class);

        $event = new SendResetPasswordSmsEvent($data);
        $this->assertSame($data, $event->getAccountRecoveryData());
    }
}
