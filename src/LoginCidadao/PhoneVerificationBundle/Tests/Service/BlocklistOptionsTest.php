<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Service;

use LoginCidadao\PhoneVerificationBundle\Service\BlocklistOptions;
use PHPUnit\Framework\TestCase;

class BlocklistOptionsTest extends TestCase
{
    public function testOptions()
    {
        $autoBlockPhoneLimit = 3;

        $options = new BlocklistOptions($autoBlockPhoneLimit);

        $this->assertTrue($options->isAutoBlockEnabled());
        $this->assertSame($autoBlockPhoneLimit, $options->getAutoBlockPhoneLimit());

        $this->assertFalse((new BlocklistOptions(0))->isAutoBlockEnabled());
    }
}
