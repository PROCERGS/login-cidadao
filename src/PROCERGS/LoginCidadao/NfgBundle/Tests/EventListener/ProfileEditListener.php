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


use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoap;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileEditListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var MeuRSHelper */
    private $meuRSHelper;

    /** @var EntityManager */
    private $em;

    /** @var NfgSoap */
    private $nfgSoap;

    /** @var string */
    private $voterRegistration;

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success event is called, session already contains new email
        $person = $this->tokenStorage->getToken()->getUser();

        $this->voterRegistration = $this->meuRSHelper->getVoterRegistration($person);
    }

    public function onProfileDocEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $this->checkVoterRegistrationChanged($user);

        if ($user->personMeuRS instanceof PersonMeuRS) {
            $this->em->persist($user->personMeuRS);
        }
    }

    private function checkVoterRegistrationChanged(PersonInterface $person)
    {
        $personVoterReg = $this->meuRSHelper->getVoterRegistration($person);
        if (null === $personVoterReg || strlen($personVoterReg) == 0) {
            // Nothing to check
            return;
        }

        if ($personVoterReg == $this->voterRegistration) {
            // Voter registration didn't change
            return;
        }

        /** @var PersonInterface $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $nfgAccessToken = $this->meuRSHelper->getNfgAccessToken($currentUser);
        if ($nfgAccessToken) {
            $userInfo = $this->nfgSoap->getUserInfo($nfgAccessToken, $personVoterReg);

            if (!$userInfo->getVoterRegistration()) {
                // Voter Registration not validated. Nothing to here
                return;
            }
        } else {
            $userInfo = null;
        }

        $otherPerson = $this->meuRSHelper->findPersonByVoterRegistration($personVoterReg);
        if ($otherPerson instanceof PersonInterface) {
            $this->handleVoterRegistrationCollision($currentUser, $otherPerson, $userInfo);
        }
    }

    private function handleVoterRegistrationCollision(
        PersonInterface $currentUser,
        PersonInterface $otherPerson,
        NfgProfile $userInfo
    ) {
        if (!$userInfo) {
            // Current user is not connected to NFG
            // TODO: throw error
        }
    }
}
