<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Tests;

use PROCERGS\LoginCidadao\CoreBundle\PROCERGSLoginCidadaoCoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PROCERGSLoginCidadaoCoreBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $security = $this->getMockBuilder('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension')
            ->disableOriginalConstructor()->getMock();
        $security->expects($this->once())->method('addSecurityListenerFactory')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\DependencyInjection\Security\Factory\LoginCidadaoFactory'));

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()->getMock();
        $containerBuilder->expects($this->once())->method('getExtension')->with('security')
            ->willReturn($security);

        $bundle = new PROCERGSLoginCidadaoCoreBundle();

        $this->assertSame('LoginCidadaoCoreBundle', $bundle->getParent());

        $bundle->build($containerBuilder);
    }
}
