<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Helper\NotificationsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Mailer\MailerInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Helper\DneHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Assetic\Exception\Exception;
use PROCERGS\LoginCidadao\CoreBundle\Helper\NfgWsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Exception\NfgException;
use PROCERGS\LoginCidadao\CoreBundle\Exception\LcValidationException;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Exception\MissingNfgAccessTokenException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\Generic\ValidationBundle\Validator\Constraints\CEPValidator;

class ProfileEditListner implements EventSubscriberInterface
{

    const PROFILE_DOC_EDIT_SUCCESS = 'lc.profile.doc.edit.success';

    private $mailer;
    private $fosMailer;
    private $tokenGenerator;
    private $router;
    private $session;
    private $security;
    private $notificationsHelper;
    private $emailUnconfirmedTime;
    protected $email;
    protected $cpf;
    private $cpfEmptyTime;
    protected $em;
    protected $dne;
    protected $voterRegistration;
    protected $nfg;
    protected $userManager;

    public function __construct(TwigSwiftMailer $mailer,
                                MailerInterface $fosMailer,
                                TokenGeneratorInterface $tokenGenerator,
                                UrlGeneratorInterface $router,
                                SessionInterface $session,
                                SecurityContextInterface $security,
                                NotificationsHelper $notificationsHelper,
                                $emailUnconfirmedTime)
    {
        $this->mailer = $mailer;
        $this->fosMailer = $fosMailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->security = $security;
        $this->notificationsHelper = $notificationsHelper;
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
            ProfileEditListner::PROFILE_DOC_EDIT_SUCCESS => 'onProfileDocEditSuccess'
        );
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success's event is called, session already contains new email
        $this->email = $this->security->getToken()
                ->getUser()
                ->getEmail();
        $this->cpf = $this->security->getToken()
                ->getUser()
                ->getCpf();
        $this->voterRegistration = $this->security->getToken()
                ->getUser()
                ->getVoterRegistration();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        
        if (!$user->getState()) {
            $steppe = ucwords(strtolower(trim($event->getForm()->get('ufsteppe')->getData())));
            if ($steppe) {
                if ($user->getCountry()) {
                    $isPreferred = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->isPreferred($user->getCountry());
                    if ($isPreferred) {
                        throw new LcValidationException('restrict.location.creation');
                    }
                } else {
                    throw new LcValidationException('required.field.country');
                }
                $repo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
                $ent = $repo->findOneBy(array(
                    'name' => $steppe,
                    'country' => $user->getCountry()
                ));
                if (!$ent) {
                    $ent = new State();
                    $ent->setName($steppe);
                    $ent->setCountry($user->getCountry());
                    $this->em->persist($ent);
                }
                $user->setState($ent);
            }
        }
        if (!$user->getCity()) {
            $steppe = ucwords(strtolower(trim($event->getForm()->get('citysteppe')->getData())));
            if ($steppe) {
                if ($user->getState()) {
                    $isPreferred = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->isPreferred($user->getState()->getCountry());
                    if ($isPreferred) {
                        throw new LcValidationException('restrict.location.creation');
                    }
                } else {
                    throw new LcValidationException('required.field.state');
                }
                $repo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
                $ent = $repo->findOneBy(array(
                    'name' => $steppe,
                    'state' => $user->getState()
                ));
                if (!$ent) {
                    $ent = new City();
                    $ent->setName($steppe);
                    $ent->setState($user->getState());
                    $this->em->persist($ent);
                }
                $user->setCity($ent);
            }
        }
        $this->checkEmailChanged($user);

        // default:
        $url = $this->router->generate('fos_user_profile_edit');

        $event->setResponse(new RedirectResponse($url));
    }

    public function onProfileDocEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $this->checkVoterRegistrationChanged($user);
        $this->checkCPFChanged($user);
    }

    public function setCpfEmptyTime($var)
    {
        $this->cpfEmptyTime = $var;
    }

    public function setEntityManager(EntityManager $var)
    {
        $this->em = $var;
    }

    public function setDneHelper(DneHelper $var)
    {
        $this->dne = $var;
    }

    public function setNfgHelper(NfgWsHelper $var)
    {
        $this->nfg = $var;
    }

    public function setUserManager($var)
    {
        $this->userManager = $var;
    }

    private function checkEmailChanged(Person &$user)
    {
        if ($user->getEmail() !== $this->email) {
            if (is_null($user->getConfirmationToken())) {
                $user->setPreviousValidEmail($this->email);
            }

            // send confirmation token to new email
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setEmailExpiration(new \DateTime("+$this->emailUnconfirmedTime"));
            $this->fosMailer->sendConfirmationEmailMessage($user);

            $this->mailer->sendEmailChangedMessage($user, $this->email);
        }
    }

    private function checkCPFChanged(Person &$user)
    {
        if ($user->getCpf() !== $this->cpf) {
            if ($user->getCpf()) {
                $user->setCpfExpiration(null);
            } else {
                $cpfExpiryDate = new \DateTime($this->cpfEmptyTime);
                $user->setCpfExpiration($cpfExpiryDate);
            }
        }
    }

    private function solveVoterRegistrationConflict(Person $user, Person $other,
                                                    $isNfgValidated = null)
    {
        $currentUser = $this->security->getToken()->getUser();
        if (is_null($isNfgValidated)) {
            try {
                $isNfgValidated = $this->nfg->isVoterRegistrationValid($currentUser,
                        $user->getVoterRegistration());
            } catch (MissingNfgAccessTokenException $e) {
                $isNfgValidated = null;
            }
        }
        if ($isNfgValidated) {
            $this->em->beginTransaction();
            try {
                $voterRegistration = $user->getVoterRegistration();
                $user->setVoterRegistration(null);
                $this->em->persist($user);

                $other->setVoterRegistration(null);
                $this->em->persist($other);

                $this->notificationsHelper->revokedVoterRegistrationNotification($other);

                $user->setVoterRegistration($voterRegistration);
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

    private function checkVoterRegistrationChanged(Person &$user)
    {
        if (null === $user->getVoterRegistration() || strlen($user->getVoterRegistration()) == 0) {
            return;
        }
        $aUser = $this->security->getToken()->getUser();
        if ($user->getVoterRegistration() != $this->voterRegistration) {
            if ($aUser->getNfgAccessToken()) {
                $this->nfg->setAccessToken($aUser->getNfgAccessToken());
                $this->nfg->setTituloEleitoral($user->getVoterRegistration());
                $nfgReturn1 = $this->nfg->consultaCadastro();
                if ($nfgReturn1['CodSitRetorno'] != 1) {
                    throw new NfgException($nfgReturn1['MsgRetorno']);
                }
                if (!isset($nfgReturn1['CodCpf'], $nfgReturn1['NomeConsumidor'],
                                $nfgReturn1['EmailPrinc'])) {
                    throw new NfgException('nfg.missing.required.fields');
                }
            }
            $personRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
            $otherPerson = $personRepo->findOneBy(array(
                'voterRegistration' => $user->getVoterRegistration()
            ));
            if ($otherPerson) {
                if (isset($nfgReturn1)) {
                    if (isset($nfgReturn1['CodSitTitulo']) && $nfgReturn1['CodSitTitulo'] != 0) {
                        if ($nfgReturn1['CodSitTitulo'] == 1) {
                            $className = $this->em->getClassMetadata(get_class($aUser))->getName();
                            $uk = $this->em->getUnitOfWork();
                            $a = $uk->getOriginalEntityData($user);
                            $uk->detach($user);

                            $otherPerson->setVoterRegistration(null);
                            $this->em->persist($otherPerson);

                            $uk->registerManaged($user, array('id' => $user->getId()),$a);

                            $this->notificationsHelper->revokedVoterRegistrationNotification($otherPerson);

                            $aNfgProfile = $aUser->getNfgProfile();
                            $aNfgProfile->setVoterRegistrationSit($nfgReturn1['CodSitTitulo']);
                            $aNfgProfile->setVoterRegistration($user->getVoterRegistration());
                            $this->em->persist($aNfgProfile);
                        } else {
                            throw new LcValidationException('voterreg.already.used.but.nfg.mismatch');
                        }
                    } else {
                        throw new LcValidationException('voterreg.already.used.but.nfg.offer');
                    }
                } else {
                    throw new LcValidationException('voterreg.already.used');
                }
            } else {
                if (isset($nfgReturn1)) {
                    if (isset($nfgReturn1['CodSitTitulo']) && $nfgReturn1['CodSitTitulo'] != 0) {
                        if ($nfgReturn1['CodSitTitulo'] == 1) {
                            $aNfgProfile = $aUser->getNfgProfile();
                            $aNfgProfile->setVoterRegistrationSit($nfgReturn1['CodSitTitulo']);
                            $aNfgProfile->setVoterRegistration($user->getVoterRegistration());
                            $this->em->persist($aNfgProfile);
                        } else {
                            throw new LcValidationException('voterreg.nfg.fixit');
                        }
                    }
                }
            }
        }
    }

}
