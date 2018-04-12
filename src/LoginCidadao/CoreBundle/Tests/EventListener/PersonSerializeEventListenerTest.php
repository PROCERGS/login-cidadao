<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\EventListener;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use LoginCidadao\CoreBundle\EventListener\PersonSerializeEventListener;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PersonSerializeEventListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ], PersonSerializeEventListener::getSubscribedEvents());
    }

    public function testListener()
    {
        $request = new Request();
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        /** @var PreSerializeEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getObject')->willReturn($person);

        /** @var ObjectEvent $postEvent */
        $postEvent = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();

        /** @var UploaderHelper $uploadHelper */
        $uploadHelper = $this->getMockBuilder('Vich\UploaderBundle\Templating\Helper\UploaderHelper')
            ->disableOriginalConstructor()->getMock();

        /** @var AssetsHelper $templateHelper */
        $templateHelper = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper')
            ->disableOriginalConstructor()->getMock();

        /** @var Kernel $kernel */
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')
            ->disableOriginalConstructor()->getMock();

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()->getMock();
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')->willReturn($request);


        $person->expects($this->once())
            ->method('prepareAPISerialize')->with(
                $uploadHelper,
                $templateHelper,
                false,
                $request
            );

        $listener = new PersonSerializeEventListener($uploadHelper, $templateHelper, $kernel, $requestStack);
        $listener->onPostSerialize($postEvent);
        $listener->onPreSerialize($event);
    }
}
