<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Model;

use LoginCidadao\CoreBundle\Model\SupportMessage;

class SupportMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportMessage()
    {
        $message = (new SupportMessage())
            ->setName($name = 'Name Here')
            ->setEmail($email = 'email@example.com')
            ->setMessage($text = 'Message');

        $this->assertSame($name, $message->getName());
        $this->assertSame($email, $message->getEmail());
        $this->assertSame($text, $message->getMessage());
    }
}
