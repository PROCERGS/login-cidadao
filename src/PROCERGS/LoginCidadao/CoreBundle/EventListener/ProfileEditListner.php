<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Mailer\MailerInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Assetic\Exception\Exception;
use PROCERGS\LoginCidadao\CoreBundle\Helper\NfgWsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Exception\NfgException;
use LoginCidadao\CoreBundle\Exception\LcValidationException;
use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Exception\MissingNfgAccessTokenException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\DynamicFormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData;
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;

class ProfileEditListner implements EventSubscriberInterface
{
    const PROFILE_DOC_EDIT_SUCCESS = 'lc.profile.doc.edit.success';

    private $mailer;
    private $fosMailer;
    private $tokenGenerator;
    private $router;
    private $session;
    private $security;
    private $emailUnconfirmedTime;
    protected $email;
    protected $cpf;
    private $cpfEmptyTime;
    protected $em;
    protected $voterRegistration;
    protected $nfg;
    protected $userManager;

    /** @var MeuRSHelper */
    protected $meuRSHelper;

    public function __construct(TwigSwiftMailer $mailer,
                                MailerInterface $fosMailer,
                                TokenGeneratorInterface $tokenGenerator,
                                UrlGeneratorInterface $router,
                                SessionInterface $session,
                                SecurityContextInterface $security,
                                $emailUnconfirmedTime)
    {
        $this->mailer               = $mailer;
        $this->fosMailer            = $fosMailer;
        $this->tokenGenerator       = $tokenGenerator;
        $this->router               = $router;
        $this->session              = $session;
        $this->security             = $security;
        $this->emailUnconfirmedTime = $emailUnconfirmedTime;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess',
            ProfileEditListner::PROFILE_DOC_EDIT_SUCCESS => 'onProfileDocEditSuccess',
            FormEvents::POST_SUBMIT => 'registerTextualLocation'
        );
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success's event is called, session already contains new email
        $person = $this->security->getToken()->getUser();

        $this->voterRegistration = $this->meuRSHelper
            ->getVoterRegistration($person);
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if (!($user instanceof PersonInterface)) {
            return;
        }
    }

    public function onProfileDocEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $this->checkVoterRegistrationChanged($user);

        if ($user->personMeuRS instanceof PersonMeuRS) {
            $this->em->persist($user->personMeuRS);
        }
    }

    public function setEntityManager(EntityManager $var)
    {
        $this->em = $var;
    }

    public function setNfgHelper(NfgWsHelper $var)
    {
        $this->nfg = $var;
    }

    public function setUserManager($var)
    {
        $this->userManager = $var;
    }

    private function solveVoterRegistrationConflict(Person $user, Person $other,
                                                    $isNfgValidated = null)
    {
        $currentUser = $this->security->getToken()->getUser();
        if (is_null($isNfgValidated)) {
            try {
                $isNfgValidated = $this->nfg->isVoterRegistrationValid($currentUser,
                    $this->meuRSHelper->getVoterRegistration($user));
            } catch (MissingNfgAccessTokenException $e) {
                $isNfgValidated = null;
            }
        }
        if ($isNfgValidated) {
            $this->em->beginTransaction();
            try {
                $voterRegistration = $this->meuRSHelper->getVoterRegistration($user);
                $this->meuRSHelper->setVoterRegistration($this->em, $user, null);
                $this->em->persist($user);

                $this->meuRSHelper->setVoterRegistration($this->em, $other, null);
                $this->em->persist($other);

                // TODO: notify user

                $this->meuRSHelper->setVoterRegistration($this->em, $user,
                    $voterRegistration);
                $this->em->persist($user);
                $this->em->flush();

                $this->em->commit();
            } catch (\Exception $up) {
                $this->em->rollback();
                throw $up;
            }
        } else {
            throw new LcValidationException('voterregistration.conflict.ask.nfg');
        }
    }

    private function checkVoterRegistrationChanged(Person &$person)
    {
        $personVoterReg = $this->meuRSHelper->getVoterRegistration($person);
        if (null === $personVoterReg || strlen($personVoterReg) == 0) {
            return;
        }

        $aUser = $this->security->getToken()->getUser();
        if ($personVoterReg == $this->voterRegistration) {
            return;
        }
        if ($this->meuRSHelper->getNfgAccessToken($aUser)) {
            $this->nfg->setAccessToken($this->meuRSHelper->getNfgAccessToken($aUser));
            $this->nfg->setTituloEleitoral($personVoterReg);
            $nfgReturn1 = $this->nfg->consultaCadastro();
            if ($nfgReturn1['CodSitRetorno'] != 1) {
                throw new NfgException($nfgReturn1['MsgRetorno']);
            }
            if (!isset($nfgReturn1['CodCpf'], $nfgReturn1['NomeConsumidor'],
                    $nfgReturn1['EmailPrinc'])) {
                throw new NfgException('nfg.missing.required.fields');
            }
        }

        $otherPerson = $this->meuRSHelper
            ->findPersonByVoterRegistration($personVoterReg);

        if ($otherPerson) {
            if (!isset($nfgReturn1)) {
                throw new LcValidationException('voterreg.already.used');
            }
            if (!(isset($nfgReturn1['CodSitTitulo']) &&
                $nfgReturn1['CodSitTitulo'] != 0)) {
                throw new LcValidationException('voterreg.already.used.but.nfg.offer');
            }
            if ($nfgReturn1['CodSitTitulo'] != 1) {
                throw new LcValidationException('voterreg.already.used.but.nfg.mismatch');
            }
            $uk = $this->em->getUnitOfWork();
            $a  = $uk->getOriginalEntityData($person);
            $uk->detach($person);

            $otherPerson->setVoterRegistration(null);
            $this->em->persist($otherPerson);

            $uk->registerManaged($person, array('id' => $person->getId()), $a);

            // TODO: notify user

            $aNfgProfile = $aUser->getNfgProfile();
            $aNfgProfile->setVoterRegistrationSit($nfgReturn1['CodSitTitulo']);
            $aNfgProfile->setVoterRegistration($personVoterReg);
            $this->em->persist($aNfgProfile);
        } else {
            if (!isset($nfgReturn1)) {
                return;
            }
            if (!(isset($nfgReturn1['CodSitTitulo']) && $nfgReturn1['CodSitTitulo']
                != 0)) {
                return;
            }
            if ($nfgReturn1['CodSitTitulo'] != 1) {
                throw new LcValidationException('voterreg.nfg.fixit');
            }
            $aNfgProfile = $aUser->getNfgProfile();
            $aNfgProfile->setVoterRegistrationSit($nfgReturn1['CodSitTitulo']);
            $aNfgProfile->setVoterRegistration($personVoterReg);
            $this->em->persist($aNfgProfile);
        }
    }

    public function setMeuRSHelper(MeuRSHelper $meuRSHelper)
    {
        $this->meuRSHelper = $meuRSHelper;

        return $this;
    }
}
