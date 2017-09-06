<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\Mailer;

use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\NfgBundle\Mailer\TwigSwiftMailer;

class TwigSwiftMailerTest extends \PHPUnit_Framework_TestCase
{
    public function testNotifyCpfLostWithHtml()
    {
        $this->runMethod('notifyCpfLost', $this->getTemplate(true));
    }

    public function testNotifyCpfLostWithoutHtml()
    {
        $this->runMethod('notifyCpfLost', $this->getTemplate(false));
    }

    public function testNotifyConnectionTransferredWithHtml()
    {
        $this->runMethod('notifyConnectionTransferred', $this->getTemplate(true));
    }

    public function testNotifyConnectionTransferredWithoutHtml()
    {
        $this->runMethod('notifyConnectionTransferred', $this->getTemplate(false));
    }

    public function runMethod($method, $template)
    {
        $swiftMailer = $this->getMockBuilder('Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();
        $swiftMailer->expects($this->atLeastOnce())->method('send');

        $urlGenerator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');

        $twig = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $twig->expects($this->atLeastOnce())->method('loadTemplate')
            ->willReturn($template);
        $twig->expects($this->once())->method('mergeGlobals')->with($this->isType('array'))
            ->willReturnCallback(function ($context) {
                return $context;
            });

        $mailer = new TwigSwiftMailer(
            $swiftMailer,
            $urlGenerator,
            $twig,
            [
                'template' => ['cpf_lost' => 'template', 'connection_moved' => 'template'],
                'email' => ['name' => 'Name', 'address' => 'some@email.com'],
            ]
        );

        $person = new Person();
        $person->setFirstName('Person');
        $mailer->$method($person);
    }

    private function getTemplate($withHtml = true)
    {
        $template = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->getMock();
        $template->expects($this->exactly(3))->method('renderBlock')
            ->willReturnCallback(function ($block) use ($withHtml) {
                if ($block === 'body_html' && !$withHtml) {
                    return null;
                }

                return $block;
            });

        return $template;
    }
}
