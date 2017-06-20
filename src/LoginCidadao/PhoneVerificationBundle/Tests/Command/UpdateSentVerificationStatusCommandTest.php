<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Command;

use LoginCidadao\PhoneVerificationBundle\Command\UpdateSentVerificationStatusCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateSentVerificationStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $container = $this->getContainer();
        $commandTester = $this->createCommandTester($container);
        $exitCode = $commandTester->execute(['command' => 'lc:phone-verification:update-sent-status']);

        $this->assertEquals(0, $exitCode, 'Returns 0 in case of success');
    }

    private function getContainer()
    {
        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())->method('iterate')
            ->willReturn(new \ArrayIterator([]));

        $repo = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getPendingUpdateSentVerificationQuery')
            ->willReturn($query);

        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with('LoginCidadaoPhoneVerificationBundle:SentVerification')
            ->willReturn($repo);

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                function ($service) use ($em, $dispatcher) {
                    switch ($service) {
                        case 'doctrine.orm.entity_manager':
                            return $em;
                        case 'event_dispatcher':
                            return $dispatcher;
                        default:
                            return null;
                    }
                }
            );

        return $container;
    }

    /**
     * @param ContainerInterface $container
     * @param Application|null $application
     *
     * @return CommandTester
     */
    private function createCommandTester(ContainerInterface $container, Application $application = null)
    {
        if (null === $application) {
            $application = new Application();
        }

        $application->setAutoExit(false);

        $command = new UpdateSentVerificationStatusCommand();
        $command->setContainer($container);

        $application->add($command);

        return new CommandTester($application->find('lc:phone-verification:update-sent-status'));
    }
}
