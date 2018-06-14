<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\EventListener\ProfileEditSubscriber;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProfileEditSubscriberTest extends TestCase
{
    public function testImplementsSubscriberInterface()
    {
        $events = ProfileEditSubscriber::getSubscribedEvents();

        $this->assertNotEmpty($events);
        $this->assertTrue(is_array($events));
    }

    public function testEditInitialize()
    {
        $person = new Person();

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')->with($person)->willReturn('123456789012');

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $this->getNfgService(),
            $this->getTokenStorage($person),
            $this->getTranslator()
        );
        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);
    }

    public function testEditSuccessNoChanges()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration($voterRegistration);
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $form = $this->getForm($person);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $this->getNfgService(),
            $this->getTokenStorage($person),
            $this->getTranslator()
        );


        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    public function testEditSuccessNoVoterRegistration()
    {
        $personMeuRS = new PersonMeuRS();
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $form = $this->getForm($person);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $this->getNfgService(),
            $this->getTokenStorage($person),
            $this->getTranslator()
        );


        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    public function testEditSuccessWithNfgNoConflict()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration(strrev($voterRegistration));
        $personMeuRS->setNfgAccessToken('access_token');
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $form = $this->getForm($person);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $nfgProfile = new NfgProfile();
        $nfgProfile->setVoterRegistrationSit(1);
        $nfg = $this->getNfgService();
        $nfg->expects($this->once())->method('getUserInfo')->willReturn($nfgProfile);

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $nfg,
            $this->getTokenStorage($person),
            $this->getTranslator()
        );

        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    public function testEditSuccessWithNfgNoConflictAndInvalidVoterRegistration()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration(strrev($voterRegistration));
        $personMeuRS->setNfgAccessToken('access_token');
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $form = $this->prepareFormError($this->getForm($person), $personMeuRS, $voterRegistration);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $nfgProfile = new NfgProfile();
        $nfgProfile->setVoterRegistrationSit(0);
        $nfg = $this->getNfgService();
        $nfg->expects($this->once())->method('getUserInfo')->willReturn($nfgProfile);

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $nfg,
            $this->getTokenStorage($person),
            $this->getTranslator()
        );

        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    public function testEditSuccessWithNfgDownNoConflict()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration(strrev($voterRegistration));
        $personMeuRS->setNfgAccessToken('access_token');
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $form = $this->getForm($person);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);

        $nfg = $this->getNfgService();
        $nfg->expects($this->once())->method('getUserInfo')->willThrowException(new NfgServiceUnavailableException());

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $nfg,
            $this->getTokenStorage($person),
            $this->getTranslator()
        );
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('notice');
        $subscriber->setLogger($logger);

        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    public function testEditSuccessWithNfgAndConflict()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration(strrev($voterRegistration));
        $personMeuRS->setNfgAccessToken('access_token');
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS->setVoterRegistration('0000');

        $form = $this->getForm($person);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);
        $meuRSHelper->expects($this->once())
            ->method('findPersonMeuRSByVoterRegistration')->willReturn($otherPersonMeuRS);

        $nfgProfile = new NfgProfile();
        $nfgProfile->setVoterRegistrationSit(1);
        $nfg = $this->getNfgService();
        $nfg->expects($this->once())->method('getUserInfo')->willReturn($nfgProfile);

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $nfg,
            $this->getTokenStorage($person),
            $this->getTranslator()
        );

        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));

        $this->assertNull($otherPersonMeuRS->getVoterRegistration());
    }

    public function testEditSuccessWithNfgDownAndConflict()
    {
        $voterRegistration = '123456789012';
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setVoterRegistration(strrev($voterRegistration));
        $personMeuRS->setNfgAccessToken('access_token');
        $person = new Person();
        $person->personMeuRS = $personMeuRS;

        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS->setVoterRegistration('0000');

        $form = $this->prepareFormError($this->getForm($person), $personMeuRS, $voterRegistration);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->once())->method('getVoterRegistration')
            ->with($person)->willReturn($voterRegistration);
        $meuRSHelper->expects($this->once())->method('getPersonMeuRS')->with($person)->willReturn($personMeuRS);
        $meuRSHelper->expects($this->once())
            ->method('findPersonMeuRSByVoterRegistration')->willReturn($otherPersonMeuRS);

        $nfgProfile = new NfgProfile();
        $nfgProfile->setVoterRegistrationSit(1);
        $nfg = $this->getNfgService();
        $nfg->expects($this->once())->method('getUserInfo')->willThrowException(new NfgServiceUnavailableException());

        $subscriber = new ProfileEditSubscriber(
            $this->getEntityManager(),
            $meuRSHelper,
            $nfg,
            $this->getTokenStorage($person),
            $this->getTranslator()
        );

        $event = new GetResponseUserEvent($person);
        $subscriber->onProfileEditInitialize($event);

        $subscriber->onProfileDocEditSuccess(new FormEvent($form, $this->getRequest()));
    }

    /**
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    /**
     * @return MockObject|MeuRSHelper
     */
    private function getMeuRSHelper()
    {
        return $this->getMockBuilder(MeuRSHelper::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return MockObject|Nfg
     */
    private function getNfgService()
    {
        return $this->getMockBuilder(Nfg::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @param null|UserInterface $currentUser
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorage($currentUser = null)
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        if ($currentUser) {
            $token = $this->createMock(TokenInterface::class);
            $token->expects($this->atLeastOnce())->method('getUser')->willReturn($currentUser);
            $tokenStorage->expects($this->atLeastOnce())->method('getToken')->willReturn($token);
        }

        return $tokenStorage;
    }

    /**
     * @return MockObject|TranslatorInterface
     */
    private function getTranslator()
    {
        return $this->createMock(TranslatorInterface::class);
    }

    /**
     * @return Request|MockObject
     */
    private function getRequest()
    {
        return $this->createMock(Request::class);
    }

    /**
     * @param null $data
     * @return MockObject|FormInterface
     */
    private function getForm($data = null)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        if ($data) {
            $form->expects($this->any())->method('getData')->willReturn($data);
        }

        return $form;
    }

    /**
     * @param FormInterface|MockObject $form
     * @param $personMeuRS
     * @param $voterRegistration
     * @return mixed
     */
    private function prepareFormError($form, $personMeuRS, $voterRegistration)
    {
        $voterRegForm = $this->getForm($voterRegistration);
        $voterRegForm->expects($this->once())->method('addError');
        $personMeuRSForm = $this->getForm($personMeuRS);
        $personMeuRSForm->expects($this->once())->method('get')->with('voterRegistration')->willReturn($voterRegForm);
        $form->expects($this->once())->method('get')->with('personMeuRS')->willReturn($personMeuRSForm);

        return $form;
    }
}
