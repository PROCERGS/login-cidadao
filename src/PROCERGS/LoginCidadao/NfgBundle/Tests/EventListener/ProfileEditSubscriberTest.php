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

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\EventListener\ProfileEditSubscriber;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use Symfony\Component\HttpFoundation\Request;

class ProfileEditSubscriberTest extends \PHPUnit_Framework_TestCase
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
        $logger = $this->getMock('Psr\Log\LoggerInterface');
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

    private function getEntityManager()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMeuRSHelper()
    {
        return $this->getMockBuilder('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getNfgService()
    {
        return $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Service\Nfg')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTokenStorage($currentUser = null)
    {
        $tokenStorage = $this->getMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );

        if ($currentUser) {
            $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
            $token->expects($this->atLeastOnce())->method('getUser')->willReturn($currentUser);
            $tokenStorage->expects($this->atLeastOnce())->method('getToken')->willReturn($token);
        }

        return $tokenStorage;
    }

    private function getTranslator()
    {
        return $this->getMock('Symfony\Component\Translation\TranslatorInterface');
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }

    private function getForm($data = null)
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        if ($data) {
            $form->expects($this->any())->method('getData')->willReturn($data);
        }

        return $form;
    }

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
