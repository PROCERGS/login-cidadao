<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\LogBundle\Tests\Entity;

use LoginCidadao\LogBundle\Entity\Log;
use Monolog\Logger;

class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $id = 123;
        $level = Logger::DEBUG;
        $levelName = 'debug';
        $context = ['context' => 123];
        $extra = ['extra' => 123];
        $message = 'The message goes here...';

        $log = (new Log())
            ->setId($id)
            ->setLevel($level)
            ->setLevelName($levelName)
            ->setContext($context)
            ->setExtra($extra)
            ->setMessage($message);
        $log->onPrePersist();

        $this->assertEquals($id, $log->getId());
        $this->assertEquals($level, $log->getLevel());
        $this->assertEquals($levelName, $log->getLevelName());
        $this->assertEquals($context, $log->getContext());
        $this->assertEquals($extra, $log->getExtra());
        $this->assertEquals($message, $log->getMessage());
        $this->assertInstanceOf('\DateTime', $log->getCreatedAt());
    }
}
