<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\CoreBundle\Exception\LcValidationException;

class ProfileEditListener implements EventSubscriberInterface
{
    const PROFILE_DOC_EDIT_SUCCESS = 'lc.profile.doc.edit.success';

    private $mailer;
    private $fosMailer;
    private $tokenGenerator;
    private $router;
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    private $emailUnconfirmedTime;

    protected $previous = [
        'email' => null,
        'cpf' => null,
    ];

    /** @var EntityManagerInterface */
    protected $em;
    protected $userManager;

    public function __construct(
        TwigSwiftMailer $mailer,
        MailerInterface $fosMailer,
        TokenGeneratorInterface $tokenGenerator,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em,
        $emailUnconfirmedTime
    ) {
        $this->mailer = $mailer;
        $this->fosMailer = $fosMailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
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
            ProfileEditListener::PROFILE_DOC_EDIT_SUCCESS => 'onProfileDocEditSuccess',
            FormEvents::POST_SUBMIT => 'registerTextualLocation',
        );
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success's event is called, session already contains new email
        $this->previous['email'] = $this->tokenStorage->getToken()
            ->getUser()
            ->getEmail();
        $this->previous['cpf'] = $this->tokenStorage->getToken()
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
                    $isPreferred = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->isPreferred(
                        $user->getCountry()
                    );
                    if ($isPreferred) {
                        throw new LcValidationException('restrict.location.creation');
                    }
                } else {
                    throw new LcValidationException('required.field.country');
                }
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:State');
                $ent = $repo->findOneBy(
                    array(
                        'name' => $steppe,
                        'country' => $user->getCountry(),
                    )
                );
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
                    $isPreferred = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->isPreferred(
                        $user->getState()->getCountry()
                    );
                    if ($isPreferred) {
                        throw new LcValidationException('restrict.location.creation');
                    }
                } else {
                    throw new LcValidationException('required.field.state');
                }
                $repo = $this->em->getRepository('LoginCidadaoCoreBundle:City');
                $ent = $repo->findOneBy(
                    array(
                        'name' => $steppe,
                        'state' => $user->getState(),
                    )
                );
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

    public function setUserManager($var)
    {
        $this->userManager = $var;
    }

    private function checkEmailChanged(Person & $user)
    {
        if ($user->getEmail() !== $this->previous['email']) {
            if (is_null($user->getConfirmationToken())) {
                $user->setPreviousValidEmail($this->previous['email']);
            }

            // send confirmation token to new email
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setEmailExpiration(new \DateTime("+$this->emailUnconfirmedTime"));
            $user->setEmailConfirmedAt(null);
            $this->fosMailer->sendConfirmationEmailMessage($user);

            $this->mailer->sendEmailChangedMessage($user, $this->previous['email']);
        }
    }

    private function checkCPFChanged(Person & $user)
    {
        if ($user->getCpf() !== $this->previous['cpf']) {
            // CPF changed
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
        if (!($data instanceof LocationSelectData)) {
            return;
        }
        $form = $event->getForm();
        if ($form->has('state_text')) {
            $stateTextInput = $form->get('state_text')->getData();
            $stateText = ucwords(strtolower(trim($stateTextInput)));
            if (!$stateText) {
                return;
            }
            if (!$data->getCountry()) {
                throw new LcValidationException('required.field.country');
            }
            $isPreferred = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->isPreferred($data->getCountry());
            if ($isPreferred) {
                throw new LcValidationException('restrict.location.creation');
            }

            $repo = $this->em->getRepository('LoginCidadaoCoreBundle:State');
            $state = $repo->findOneBy(
                array(
                    'name' => $stateText,
                    'country' => $data->getCountry(),
                )
            );
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
        if (!($data instanceof LocationSelectData)) {
            return;
        }
        $form = $event->getForm();
        if ($form->has('city_text')) {
            $cityTextInput = $form->get('city_text')->getData();
            $cityText = ucwords(strtolower(trim($cityTextInput)));
            if (!$cityText) {
                return;
            }
            if (!$data->getState()) {
                throw new LcValidationException('required.field.state');
            }
            $isPreferred = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->isPreferred($data->getCountry());
            if ($isPreferred) {
                throw new LcValidationException('restrict.location.creation');
            }

            $repo = $this->em->getRepository('LoginCidadaoCoreBundle:City');
            $city = $repo->findOneBy(
                array(
                    'name' => $cityText,
                    'state' => $data->getState(),
                )
            );
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
