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

use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions;

class PhoneVerificationOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $length = 10;
        $useNumbers = true;
        $useLower = true;
        $useUpper = true;
        $caseSensitive = true;
        $smsResendTimeout = '+10 minutes';
        $tokenLength = 5;

        $options = new PhoneVerificationOptions(
            $length,
            $useNumbers,
            $caseSensitive,
            $useLower,
            $useUpper,
            $smsResendTimeout,
            $tokenLength
        );

        $this->assertEquals($length, $options->getLength());
        $this->assertTrue($options->isUseNumbers());
        $this->assertTrue($options->isUseLowerCase());
        $this->assertTrue($options->isUseUpperCase());
        $this->assertTrue($options->isCaseSensitive());
        $this->assertEquals($smsResendTimeout, $options->getSmsResendTimeout());
        $this->assertEquals($tokenLength, $options->getVerificationTokenLength());
    }
}
