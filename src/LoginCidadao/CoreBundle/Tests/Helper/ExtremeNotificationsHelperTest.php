<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Helper;

use LoginCidadao\CoreBundle\Helper\ExtremeNotificationsHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class ExtremeNotificationsHelperTest extends TestCase
{

    public function testHelper()
    {
        $id = 'the.message.id';
        $number = 3;
        $parameters = ['param1' => 'value1', 'param2' => 'value2'];
        $translated = 'the translated message';

        $flashBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
            ->disableOriginalConstructor()->getMock();
        $flashBag->expects($this->exactly(2))
            ->method('add')
            ->with('alert.unconfirmed.email', $translated);

        /** @var Session|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->exactly(2))->method('getFlashBag')->willReturn($flashBag);

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())->method('trans')->with($id, $parameters)->willReturn($translated);
        $translator->expects($this->once())->method('transChoice')->with($id, $number, $parameters)
            ->willReturn($translated);

        $helper = new ExtremeNotificationsHelper($session, $translator);
        $helper->add($id, $parameters);
        $helper->addTransChoice($id, $number, $parameters);
    }
}
