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
use PROCERGS\LoginCidadao\NotificationBundle\Helper\NotificationsHelper;
use PROCERGS\LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Mailer\MailerInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Assetic\Exception\Exception;
use PROCERGS\LoginCidadao\CoreBundle\Exception\LcValidationException;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\DynamicFormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData;
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

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
        $this->mailer               = $mailer;
        $this->fosMailer            = $fosMailer;
        $this->tokenGenerator       = $tokenGenerator;
        $this->router               = $router;
        $this->session              = $session;
        $this->security             = $security;
        $this->notificationsHelper  = $notificationsHelper;
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
        $this->email             = $this->security->getToken()
            ->getUser()
            ->getEmail();
        $this->cpf               = $this->security->getToken()
            ->getUser()
            ->getCpf();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if ($user instanceof DynamicFormData) {
            $this->checkEmailChanged($user->getPerson());
        }
        if (!($user instanceof PersonInterface)) {
            return;
        }

        if (!$user->getState() && $event->getForm()->has('ufsteppe')) {
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
                $ent  = $repo->findOneBy(array(
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
        if (!$user->getCity() && $event->getForm()->has('citysteppe')) {
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
                $ent  = $repo->findOneBy(array(
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

    public function registerTextualLocation(\Symfony\Component\Form\FormEvent $event)
    {
        $this->registerTextualState($event);
        $this->registerTextualCity($event);
    }

    private function registerTextualState(\Symfony\Component\Form\FormEvent $event)
    {
        $data = $event->getForm()->getData();
        if (!($data instanceof SelectData)) {
            return;
        }
        $form = $event->getForm();
        if ($form->has('state_text')) {
            $stateTextInput = $form->get('state_text')->getData();
            $stateText      = ucwords(strtolower(trim($stateTextInput)));
            if (!$stateText) {
                return;
            }
            if (!$data->getCountry()) {
                throw new LcValidationException('required.field.country');
            }
            $isPreferred = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->isPreferred($data->getCountry());
            if ($isPreferred) {
                throw new LcValidationException('restrict.location.creation');
            }

            $repo  = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
            $state = $repo->findOneBy(array(
                'name' => $stateText,
                'country' => $data->getCountry()
            ));
            if (!$state) {
                $state = new State();
                $state->setName($stateText);
                $state->setCountry($data->getCountry());
                $this->em->persist($state);
                $this->em->flush($state);
            }
            $data->setState($state)
                ->setCity(null);
        }
    }

    private function registerTextualCity(\Symfony\Component\Form\FormEvent $event)
    {
        $data = $event->getForm()->getData();
        if (!($data instanceof SelectData)) {
            return;
        }
        $form = $event->getForm();
        if ($form->has('city_text')) {
            $cityTextInput = $form->get('city_text')->getData();
            $cityText      = ucwords(strtolower(trim($cityTextInput)));
            if (!$cityText) {
                return;
            }
            if (!$data->getState()) {
                throw new LcValidationException('required.field.state');
            }
            $isPreferred = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->isPreferred($data->getCountry());
            if ($isPreferred) {
                throw new LcValidationException('restrict.location.creation');
            }

            $repo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
            $city = $repo->findOneBy(array(
                'name' => $cityText,
                'state' => $data->getState()
            ));
            if (!$city) {
                $city = new City();
                $city->setName($cityText);
                $city->setState($data->getState());
                $this->em->persist($city);
            }
            $data->setCity($city);
        }
    }
}
