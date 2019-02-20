<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Tests\Twig;

use LoginCidadao\SupportBundle\Twig\SupportExtensions;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class SupportExtensionsTest extends TestCase
{
    public function testMask()
    {
        $ext = new SupportExtensions();

        $filters = $ext->getFilters();

        /** @var TwigFilter $filter */
        $filter = $filters[0];

        $this->assertCount(1, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filter);
        $this->assertEquals('support_mask', $filter->getName());

        $this->assertEquals('test', $ext->mask('test', false));
        $this->assertEquals('****', $ext->mask('test', true));
        $this->assertEquals('*', $ext->mask('test', true, 1));
        $this->assertEquals('****, *****.', $ext->mask('Test, hello.', true));
        $this->assertEquals('*****', $ext->mask('12345', true));
        $this->assertEquals('****@*******.***', $ext->mask('mask@example.com', true));
    }
}
