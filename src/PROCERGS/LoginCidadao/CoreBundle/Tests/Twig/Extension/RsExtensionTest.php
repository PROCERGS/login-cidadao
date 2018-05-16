<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Tests\Twig\Extension;

use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\CoreBundle\Twig\Extension\RsExtension;

class RsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtension()
    {
        $person = new Person();
        $personMeuRS = (new PersonMeuRS())
            ->setPerson($person);

        /** @var \PHPUnit_Framework_MockObject_MockObject|MeuRSHelper $helper */
        $helper = $this->getMockBuilder('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper')
            ->disableOriginalConstructor()->getMock();
        $helper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $ext = new RsExtension($helper);
        $filters = $ext->getFilters();

        $this->assertNotEmpty($filters);
        $this->assertCount(1, $filters);
        $filter = $filters[0];

        $this->assertInstanceOf('\Twig_SimpleFilter', $filter);
        $this->assertSame('personRS', $filter->getName());

        $this->assertSame('person_rs_twig_extension', $ext->getName());
        $this->assertSame($personMeuRS, $ext->getPersonRS($person));
    }
}
