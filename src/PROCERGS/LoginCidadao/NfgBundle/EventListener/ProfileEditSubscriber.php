<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\EventListener;


use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use LoginCidadao\CoreBundle\EventListener\ProfileEditListener;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProfileEditSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var MeuRSHelper */
    private $meuRSHelper;

    /** @var EntityManager */
    private $em;

    /** @var Nfg */
    private $nfg;

    /** @var string */
    private $voterRegistration;

    /** @var TranslatorInterface */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    /**
     * ProfileEditListener constructor.
     * @param EntityManager $em
     * @param MeuRSHelper $meuRSHelper
     * @param Nfg $nfg
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManager $em,
        MeuRSHelper $meuRSHelper,
        Nfg $nfg,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->meuRSHelper = $meuRSHelper;
        $this->em = $em;
        $this->nfg = $nfg;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
            ProfileEditListener::PROFILE_DOC_EDIT_SUCCESS => 'onProfileDocEditSuccess',
            FormEvents::POST_SUBMIT => 'registerTextualLocation',
        );
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success event is called, session already contains new email
        $person = $this->tokenStorage->getToken()->getUser();

        $this->voterRegistration = $this->meuRSHelper->getVoterRegistration($person);
    }

    public function onProfileDocEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $this->checkVoterRegistrationChanged($event);

        if ($user->personMeuRS instanceof PersonMeuRS) {
            $this->em->persist($user->personMeuRS);
        }
    }

    private function checkVoterRegistrationChanged(FormEvent $event)
    {
        /** @var PersonInterface $person */
        $person = $event->getForm()->getData();
        $personMeuRS = $this->meuRSHelper->getPersonMeuRS($person);
        $personVoterReg = $personMeuRS->getVoterRegistration();
        if (null === $personVoterReg || strlen($personVoterReg) == 0) {
            // Nothing to check
            return;
        }

        if ($personVoterReg == $this->voterRegistration) {
            // Voter registration didn't change
            return;
        }

        $nfgAccessToken = $personMeuRS->getNfgAccessToken();
        $userInfo = null;
        if ($nfgAccessToken) {
            try {
                $userInfo = $this->nfg->getUserInfo($nfgAccessToken, $personVoterReg, false);
                $this->nfg->syncNfgProfile($userInfo);
            } catch (NfgServiceUnavailableException $e) {
                // NFG Service unavailable
                $userInfo = null;
                if ($this->logger) {
                    $this->logger->notice('NFG Service unavailable. Skipping Voter Registration check');
                }
            }

            // Current User is connected to NFG but the Voter Registration didn't match
            if (false === $this->validateVoterRegistration($event, $userInfo)) {
                return;
            }
        }

        $otherPersonMeuRS = $this->meuRSHelper->findPersonMeuRSByVoterRegistration($personVoterReg);
        if ($otherPersonMeuRS instanceof PersonMeuRS) {
            $this->handleVoterRegistrationCollision($event, $personMeuRS, $otherPersonMeuRS, $userInfo);
        }
    }

    private function handleVoterRegistrationCollision(
        FormEvent $event,
        PersonMeuRS $currentPersonMeuRS,
        PersonMeuRS $otherPersonMeuRS,
        NfgProfile $userInfo = null
    ) {
        if (!$userInfo) {
            // Current user is not connected to NFG
            $message = $this->translator->trans('nfg.error.voter_registration.in_use');
            $event->getForm()->get('personMeuRS')->get('voterRegistration')->addError(new FormError($message));

            return;
        }

        // The other person can't be connected to NFG since the current user is, so current user has higher priority
        $otherPersonMeuRS->setVoterRegistration(null);
        $currentPersonMeuRS->setVoterRegistration($userInfo->getVoterRegistration());
        $this->em->persist($otherPersonMeuRS);
        // TODO: notify $otherPersonMeuRS
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function validateVoterRegistration(FormEvent $event, NfgProfile $userInfo = null)
    {
        if ($userInfo === null) {
            return true;
        }

        if ($userInfo->getVoterRegistrationSit() != 1) {
            $message = $this->translator->trans('nfg.error.voter_registration.invalid');
            $event->getForm()->get('personMeuRS')->get('voterRegistration')->addError(new FormError($message));

            return false;
        }

        return true;
    }
}
