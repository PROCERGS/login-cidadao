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
use PROCERGS\LoginCidadao\CoreBundle\Helper\NotificationsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Mailer\MailerInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Helper\DneHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Assetic\Exception\Exception;
use PROCERGS\LoginCidadao\CoreBundle\Helper\NfgWsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Exception\NfgException;
use PROCERGS\LoginCidadao\CoreBundle\Exception\LcValidationException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

class ProfileEditListner implements EventSubscriberInterface
{

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

    protected $voterReg;

    protected $nfg;

    protected $userManager;

    public function __construct(TwigSwiftMailer $mailer, MailerInterface $fosMailer, TokenGeneratorInterface $tokenGenerator, UrlGeneratorInterface $router, SessionInterface $session, SecurityContextInterface $security, NotificationsHelper $notificationsHelper, $emailUnconfirmedTime)
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
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess'
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
        $this->voterReg = $this->security->getToken()
            ->getUser()
            ->getVoterReg();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $aUser = $this->security->getToken()->getUser();
        if ($user->getVoterReg() != $this->voterReg) {
            if ($aUser->getNfgAccessToken()) {
                $this->nfg->setAccessToken($aUser->getNfgAccessToken());
                $this->nfg->setTituloEleitoral($user->getVoterReg());
                $nfgReturn1 = $this->nfg->consultaCadastro();
                if ($nfgReturn1['CodSitRetorno'] != 1) {
                    throw new NfgException($nfgReturn1['MsgRetorno']);
                }
                if (! isset($nfgReturn1['CodCpf'], $nfgReturn1['NomeConsumidor'], $nfgReturn1['EmailPrinc'])) {
                    throw new NfgException('nfg.missing.required.fields');
                }
            }
            $personRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
            $otherPerson = $personRepo->findOneBy(array(
                'voterReg' => $user->getVoterReg()
            ));
            if ($otherPerson) {
                if (isset($nfgReturn1)) {
                    if (isset($nfgReturn1['CodSitTitulo']) && $nfgReturn1['CodSitTitulo'] != 0) {
                        if ($nfgReturn1['CodSitTitulo'] == 1) {
                            $this->em->flush();

                            $otherPerson->setVoterReg(null);
                            $this->em->persist($otherPerson);
                            $this->em->flush();

                            $notification = new Notification();
                            $notification->setPerson($otherPerson)
                            ->setIcon('glyphicon glyphicon-exclamation-sign')
                            ->setLevel(Notification::LEVEL_IMPORTANT)
                            ->setTitle('notification.nfg.revoked.voterreg.title')
                            ->setShortText('notification.nfg.revoked.voterreg.message.short')
                            ->setText('notification.nfg.revoked.voterreg.message');
                            $this->em->persist($notification);

                            $aNfgProfile = $aUser->getNfgProfile();
                            $aNfgProfile->setVoterRegSit($nfgReturn1['CodSitTitulo']);
                            $aNfgProfile->setVoterReg($user->getVoterReg());
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

            }
        }
        if ($user->getEmail() !== $this->email) {
            if (is_null($user->getConfirmationToken())) {
                $user->setPreviousValidEmail($this->email);
            }

            // send confirmation token to new email
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setEmailExpiration(new \DateTime("+$this->emailUnconfirmedTime"));
            $this->fosMailer->sendConfirmationEmailMessage($user);

            $this->notificationsHelper->enforceUnconfirmedEmailNotification($user);
            $this->mailer->sendEmailChangedMessage($user, $this->email);
        }
        if ($user->getCpf() !== $this->cpf) {
            if ($user->getCpf()) {
                $user->setCpfExpiration(null);
            } else {
                $cpfExpiryDate = new \DateTime($this->cpfEmptyTime);
                $user->setCpfExpiration($cpfExpiryDate);
            }
        }
        if ($user->getCep()) {
            $ceps = $this->dne->findByCep($user->getCep());
            if (is_numeric($ceps['codigoMunIBGE'])) {
                $cityRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
                $city = $cityRepo->findOneBy(array(
                    'id' => $ceps['codigoMunIBGE']
                ));
                if (! $city) {
                    $city = new City();
                    $city->setId($ceps['codigoMunIBGE']);
                    $city->setName($ceps['localidade']);
                    $this->em->persist($city);
                }
                $user->setCity($city);
                $ufRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Uf');
                $uf = $ufRepo->findOneBy(array(
                    'acronym' => $ceps['uf']
                ));
                if (! $uf) {
                    throw Exception('uf not found');
                }
                $user->setUf($uf);
                $user->setAdress($ceps['logradouroExtenso']);
            }
        }
        // default:
        $url = $this->router->generate('fos_user_profile_edit');

        $event->setResponse(new RedirectResponse($url));
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
}
