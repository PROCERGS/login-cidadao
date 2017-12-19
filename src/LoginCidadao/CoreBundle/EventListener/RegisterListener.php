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
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Service\RegisterRequestedScope;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;
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
    private $em;

    private $lcSupportedScopes;

    /** @var RegisterRequestedScope */
    private $registerRequestedScope;

    /** @var ClientRepository */
    public $clientRepository;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    /** @var string */
    private $defaultClientUid;

    /** @var string */
    private $defaultCountry;

    public function __construct(
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        RegisterRequestedScope $registerRequestedScope,
        ClientRepository $clientRepository,
        EntityManagerInterface $em,
        SubjectIdentifierService $subjectIdentifierService,
        $emailUnconfirmedTime,
        $lcSupportedScopes,
        $defaultClientUid,
        $defaultCountry
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
        $this->em = $em;
        $this->subjectIdentifierService = $subjectIdentifierService;
        $this->defaultClientUid = $defaultClientUid;
        $this->defaultCountry = $defaultCountry;
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
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
        );
    }

    public function onRegistrationInitialize(GetResponseUserEvent $event)
    {
        $this->preparePreFilledRegistrationForm($event);
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

    private function preparePreFilledRegistrationForm(GetResponseUserEvent $event)
    {
        $request = $event->getRequest();
        $data = $request->get('prefill');
        if (!is_array($data) || empty($data)) {
            return;
        }

        $user = $event->getUser();

        if (!$user instanceof PersonInterface) {
            return;
        }

        foreach ($data as $key => $value) {
            $this->setUserInfo($user, $key, $value);
        }

        $keys = array_keys($data);
        if (!empty($keys)) {
            $this->registerRequestedScope->registerScope($keys, $request->getSession());
        }
    }

    private function setUserInfo(PersonInterface $user, $key, $value)
    {
        switch ($key) {
            case 'email':
                $user->setEmail($value);
                break;
            case 'name':
            case 'first_name':
                $user->setFirstName($value);
                break;
            case 'surname':
            case 'last_name':
                $user->setSurname($value);
                break;
            case 'full_name':
                $names = explode(' ', $value, 2);
                $user->setFirstName($names[0]);
                $user->setSurname($names[1]);
                break;
            case 'cpf':
                $user->setCpf($value);
                break;
            case 'mobile':
            case 'phone_number':
                try {
                    $util = PhoneNumberUtil::getInstance();
                    $phoneNumber = $util->parse($value, $this->defaultCountry);
                    $user->setMobile($phoneNumber);
                } catch (NumberParseException $e) {
                    // TODO: log and continue
                    continue;
                }
                break;
            case 'birthday':
            case 'birthdate':
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                if ($date instanceof \DateTime) {
                    $user->setBirthdate($date);
                }
                break;
            default:
                continue;
        }
    }
}
