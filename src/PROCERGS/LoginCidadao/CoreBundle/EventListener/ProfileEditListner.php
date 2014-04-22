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

class ProfileEditListner implements EventSubscriberInterface
{

    private $mailer;

    private $fosMailer;

    private $tokenGenerator;

    private $router;

    private $session;

    private $security;

    /**
     *
     * @var NotificationsHelper
     */
    private $notificationsHelper;

    private $emailUnconfirmedTime;

    private $email;

    private $cpf;

    private $cpfEmptyTime;

    protected $em;

    protected $dne;

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
        $this->email = $this->security->getToken()->getUser()->getEmail();
        $this->cpf = $this->security->getToken()->getUser()->getCpf();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
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
            $user->setCpfNfg(null);
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
                $city = $cityRepo->findOneBy(array('id' => $ceps['codigoMunIBGE']));
                if (!$city) {
                    $city = new City();
                    $city->setId($ceps['codigoMunIBGE']);
                    $city->setName($ceps['localidade']);
                    $this->em->persist($city);                    
                }
                $user->setCity($city);
                $ufRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Uf');                
                $uf = $ufRepo->findOneBy(array('acronym' => $ceps['uf']));
                if (!$uf) {
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
}
