<?php

namespace LoginCidadao\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Service\RegisterRequestedScope;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;
use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\Authorization;

class RegisterListener implements EventSubscriberInterface
{
    private $router;

    /** \Symfony\Component\HttpFoundation\Session\Session * */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    /** @var MailerInterface */
    private $mailer;

    /** @var TokenGeneratorInterface */
    private $tokenGenerator;

    private $emailUnconfirmedTime;

    /** @var EntityManagerInterface */
    protected $em;
    private $lcSupportedScopes;

    /** @var RegisterRequestedScope */
    private $registerRequestedScope;

    /** @var ClientRepository */
    public $clientRepository;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    /** @var string */
    private $defaultClientUid;

    public function __construct(
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        RegisterRequestedScope $registerRequestedScope,
        ClientRepository $clientRepository,
        SubjectIdentifierService $subjectIdentifierService,
        $emailUnconfirmedTime,
        $lcSupportedScopes,
        $defaultClientUid
    ) {
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->emailUnconfirmedTime = $emailUnconfirmedTime;
        $this->lcSupportedScopes = $lcSupportedScopes;
        $this->registerRequestedScope = $registerRequestedScope;
        $this->clientRepository = $clientRepository;
        $this->subjectIdentifierService = $subjectIdentifierService;
        $this->defaultClientUid = $defaultClientUid;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRM => 'onEmailConfirmed',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setEmailExpiration(new \DateTime("+$this->emailUnconfirmedTime"));
        }

        $key = '_security.main.target_path';
        if ($this->session->has($key)) {
            //this is to be catch by loggedinUserListener.php
            $event->setResponse(new RedirectResponse($this->router->generate('lc_home')));

            return;
        }

        if (!$user->getUsername()) {
            $email = explode('@', $user->getEmailCanonical(), 2);
            $username = $email[0];
        } else {
            $username = $user->getUsername();
        }
        if (!UsernameValidator::isUsernameValid($username)) {
            $user->setUsername(Uuid::uuid4()->toString());
        }
        $url = $this->router->generate('fos_user_profile_edit');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        /** @var PersonInterface $user */
        $user = $event->getUser();

        /** @var Client $client */
        $client = $this->clientRepository->findOneBy(['uid' => $this->defaultClientUid]);

        $auth = new Authorization();
        $auth->setPerson($user);
        $auth->setClient($client);
        $auth->setScope(explode(' ', $this->lcSupportedScopes));

        $subjectIdentifier = $this->subjectIdentifierService->getSubjectIdentifier($user, $client->getMetadata());
        $sub = new SubjectIdentifier();
        $sub->setPerson($user)
            ->setClient($client)
            ->setSubjectIdentifier($subjectIdentifier);

        $this->em->persist($auth);
        $this->em->persist($sub);
        $this->em->flush();

        $this->mailer->sendConfirmationEmailMessage($user);

        if (strlen($user->getPassword()) == 0) {
            // TODO: DEPRECATE NOTIFICATIONS
            // TODO: create an optional task offering users to set a password
            //$this->notificationsHelper->enforceEmptyPasswordNotification($user);
        }

        $this->registerRequestedScope->clearRequestedScope($event->getRequest());
    }

    public function onEmailConfirmed(GetResponseUserEvent $event)
    {
        $event->getUser()->setEmailConfirmedAt(new \DateTime());
        $event->getUser()->setEmailExpiration(null);

        $this->session->getFlashBag()->add(
            'success',
            $this->translator->trans(
                'registration.confirmed',
                array(
                    '%username%' => $event->getUser()->getFirstName(),
                ),
                'FOSUserBundle'
            )
        );
        $this->session->getFlashBag()->get('alert.unconfirmed.email');

        $url = $this->router->generate('fos_user_profile_edit');
        $event->setResponse(new RedirectResponse($url));
    }

    public function setEntityManager(EntityManager $var)
    {
        $this->em = $var;
    }
}
