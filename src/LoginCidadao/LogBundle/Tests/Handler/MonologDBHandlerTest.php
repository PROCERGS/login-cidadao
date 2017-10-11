<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\LogBundle\Tests\Handler;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\LogBundle\Handler\MonologDBHandler;

class MonologDBHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $record = [
        'message' => 'Some message',
        'level' => 100,
        'level_name' => 'debug',
        'extra' => ['this' => 'is extra'],
        'context' => ['that is' => 'the context'],
    ];

    public function testWriteSuccessWithCache()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\LogBundle\Entity\Log'));

        $cache = $this->getCache();
        $cache->expects($this->once())->method('contains')
            ->with(MonologDBHandler::DISABLE_LOGGING_FLAG_KEY)->willReturn(false);

        $handler = new MonologDBHandler($em);
        $handler->setCacheProvider($cache);
        $handler->handle($this->record);
    }

    public function testWriteIgnoredWithCache()
    {
        $em = $this->getEntityManager();
        $cache = $this->getCache();
        $cache->expects($this->once())->method('contains')
            ->with(MonologDBHandler::DISABLE_LOGGING_FLAG_KEY)->willReturn(true);

        $handler = new MonologDBHandler($em);
        $handler->setCacheProvider($cache);
        $handler->handle($this->record);
    }

    public function testWriteSuccessWithoutCache()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\LogBundle\Entity\Log'));

        $handler = new MonologDBHandler($em);
        $handler->handle($this->record);
    }

    public function testWriteFailureWithCache()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\LogBundle\Entity\Log'))
            ->willThrowException(new \RuntimeException('Something failed!'));
        $em->expects($this->never())->method('flush');

        $cache = $this->getCache();
        $cache->expects($this->once())->method('contains')
            ->with(MonologDBHandler::DISABLE_LOGGING_FLAG_KEY)->willReturn(false);
        $cache->expects($this->once())->method('save')
            ->with(MonologDBHandler::DISABLE_LOGGING_FLAG_KEY, true, MonologDBHandler::LIFETIME);

        $handler = new MonologDBHandler($em);
        $handler->setCacheProvider($cache);
        $handler->handle($this->record);
    }

    public function testWriteFailureWithoutCache()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\LogBundle\Entity\Log'))
            ->willThrowException(new \RuntimeException('Something failed!'));
        $em->expects($this->never())->method('flush');

        $handler = new MonologDBHandler($em);
        $handler->handle($this->record);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheProvider
     */
    private function getCache()
    {
        return $this->getMock('Doctrine\Common\Cache\CacheProvider');
    }
}
