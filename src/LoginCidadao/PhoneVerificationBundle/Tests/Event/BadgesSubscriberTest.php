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
use PHPUnit\Framework\TestCase;

class BadgesSubscriberTest extends TestCase
{
    private function getEmWithRepo()
    {
        $repo = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        return $em;
    }

    private function getPhoneVerificationService()
    {
        $class = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface';
        $phoneVerificationService = $this->createMock($class);

        return $phoneVerificationService;
    }

    private function getSubscriber(
        $enabled = true,
        PhoneVerificationServiceInterface $phoneVerificationService = null,
        EntityManagerInterface $em = null
    ) {
        $phoneVerificationService = $phoneVerificationService ?: $this->getPhoneVerificationService();
        $em = $em ?: $this->createMock('Doctrine\ORM\EntityManagerInterface');

        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');

        return new BadgesSubscriber($phoneVerificationService, $translator, $em, $enabled);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(BadgesSubscriber::class, $this->getSubscriber(true));
        $this->assertInstanceOf(BadgesSubscriber::class, $this->getSubscriber(false));
    }

    public function testOnBadgeEvaluateDisabled()
    {
        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertNull($this->getSubscriber(false)->onBadgeEvaluate($event));
    }

    public function testOnBadgeEvaluate()
    {
        $phone = $this->getMockBuilder('libphonenumber\PhoneNumber')
            ->disableOriginalConstructor()
            ->getMock();
        $person = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getMobile')->willReturn($phone);

        $phoneVerification = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
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
        $person = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
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
        $person = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
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
        $badge = $this->createMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
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
        $badge = $this->createMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
        $badge->expects($this->atLeastOnce())->method('getName')->willReturn('UNKNOWN');

        $phoneVerificationService = $this->getPhoneVerificationService();

        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\ListBearersEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getBadge')->willReturn($badge);

        $this->getSubscriber(true, $phoneVerificationService)->onListBearers($event);
    }
}
