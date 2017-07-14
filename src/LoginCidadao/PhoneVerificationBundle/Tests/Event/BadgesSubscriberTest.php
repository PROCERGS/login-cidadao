<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Event;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\PhoneVerificationBundle\Event\BadgesSubscriber;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;

class BadgesSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private function getEmWithRepo()
    {
        $repo = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        return $em;
    }

    private function getPhoneVerificationService()
    {
        $class = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->getMock($class);

        return $phoneVerificationService;
    }

    private function getSubscriber(
        $enabled = true,
        PhoneVerificationServiceInterface $phoneVerificationService = null,
        EntityManagerInterface $em = null
    ) {
        $phoneVerificationService = $phoneVerificationService ?: $this->getPhoneVerificationService();
        $em = $em ?: $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        return new BadgesSubscriber($phoneVerificationService, $translator, $em, $enabled);
    }

    public function testConstructor()
    {
        $this->getSubscriber(true);
        $this->getSubscriber(false);
    }

    public function testOnBadgeEvaluateDisabled()
    {
        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->getSubscriber(false)->onBadgeEvaluate($event);
    }

    public function testOnBadgeEvaluate()
    {
        $phone = $this->getMockBuilder('libphonenumber\PhoneNumber')
            ->disableOriginalConstructor()
            ->getMock();
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getMobile')->willReturn($phone);

        $phoneVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $phoneVerification->expects($this->once())->method('isVerified')->willReturn(true);

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getPhoneVerification')
            ->with($person, $phone)
            ->willReturn($phoneVerification);

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getPerson')->willReturn($person);

        $this->getSubscriber(true, $phoneVerificationService)->onBadgeEvaluate($event);
    }

    public function testOnBadgeEvaluateNoPhone()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getMobile')->willReturn(null);

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getPerson')->willReturn($person);

        $this->getSubscriber(true)->onBadgeEvaluate($event);
    }

    public function testOnBadgeEvaluateNoPhoneVerification()
    {
        $phone = $this->getMockBuilder('libphonenumber\PhoneNumber')
            ->disableOriginalConstructor()
            ->getMock();
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getMobile')->willReturn($phone);

        $phoneVerificationService = $this->getPhoneVerificationService();
        $phoneVerificationService->expects($this->once())->method('getPhoneVerification')
            ->with($person, $phone)
            ->willReturn(null);

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getPerson')->willReturn($person);

        $this->getSubscriber(true, $phoneVerificationService)->onBadgeEvaluate($event);
    }

    public function testOnListBearersWithKnownBadge()
    {
        $badge = $this->getMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
        $badge->expects($this->atLeastOnce())->method('getName')->willReturn('phone_verified');

        $phoneVerificationService = $this->getPhoneVerificationService();
        $em = $this->getEmWithRepo();

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\ListBearersEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getBadge')->willReturn($badge);

        $this->getSubscriber(true, $phoneVerificationService, $em)->onListBearers($event);
    }

    public function testOnListBearersWithUnknownBadge()
    {
        $badge = $this->getMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
        $badge->expects($this->atLeastOnce())->method('getName')->willReturn('UNKNOWN');

        $phoneVerificationService = $this->getPhoneVerificationService();

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\ListBearersEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getBadge')->willReturn($badge);

        $this->getSubscriber(true, $phoneVerificationService)->onListBearers($event);
    }
}
