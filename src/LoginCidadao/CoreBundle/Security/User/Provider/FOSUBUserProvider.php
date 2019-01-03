<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security\User\Provider;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\Exception\DuplicateEmailException;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use LoginCidadao\CoreBundle\Security\Exception\MissingEmailException;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FOSUBUserProvider extends BaseClass
{

    /** @var UserManagerInterface|UserManager */
    protected $userManager;

    /** @var SessionInterface */
    private $session;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var FactoryInterface */
    private $formFactory;

    /** @var ValidatorInterface */
    private $validator;

    /** @var RequestStack */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param SessionInterface $session
     * @param EventDispatcherInterface $dispatcher
     * @param FactoryInterface $formFactory
     * @param ValidatorInterface $validator
     * @param RequestStack $requestStack
     * @param array $properties Property mapping.
     * @internal param ContainerInterface $container
     */
    public function __construct(
        UserManagerInterface $userManager,
        SessionInterface $session,
        EventDispatcherInterface $dispatcher,
        FactoryInterface $formFactory,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        array $properties
    ) {
        parent::__construct($userManager, $properties);
        $this->userManager = $userManager;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     * @throws AlreadyLinkedAccount
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $username = $response->getUsername();

        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        $setter_username = $setter.'Username';

        /** @var PersonInterface $existingUser|null */
        $existingUser = $this->userManager->findUserBy(array("{$service}Id" => $username));
        if ($existingUser instanceof UserInterface && $existingUser->getId() != $user->getId()) {
            throw new AlreadyLinkedAccount();
        }

        $screenName = $response->getNickname();
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $user->$setter_username($screenName);

        if ($service === 'facebook') {
            $this->setFacebookData($user, $response->getData());
        }

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     * @throws MissingEmailException
     * @throws DuplicateEmailException
     * @throws \Exception
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userInfo = $this->getUserInfo($response);
        $service = $response->getResourceOwner()->getName();

        $user = $this->userManager->findUserBy(array("{$service}Id" => $userInfo['id']));

        if ($user instanceof PersonInterface) {
            $user = parent::loadUserByOAuthUserResponse($response);

            $serviceName = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($serviceName).'AccessToken';

            $user->$setter($response->getAccessToken());

            return $user;
        }

        $userInfo = $this->checkEmail($service, $userInfo);

        /** @var PersonInterface $user */
        $user = $this->userManager->createUser();
        $this->setUserInfo($user, $userInfo, $service);

        if ($userInfo['first_name']) {
            $user->setFirstName($userInfo['first_name']);
        }
        if ($userInfo['family_name']) {
            $user->setSurname($userInfo['family_name']);
        }

        if ($service === 'facebook') {
            $this->setFacebookData($user, $response->getData());
        }

        $username = Uuid::uuid4()->toString();
        if (!UsernameValidator::isUsernameValid($username)) {
            $username = UsernameValidator::getValidUsername();
        }

        $availableUsername = $this->userManager->getNextAvailableUsername(
            $username,
            10,
            Uuid::uuid4()->toString()
        );

        $user->setUsername($availableUsername);
        $user->setEmail($userInfo['email']);
        $user->setPassword('');
        $user->setEnabled(true);
        $this->userManager->updateCanonicalFields($user);

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($user, null, ['LoginCidadaoProfile']);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                if ($error->getPropertyPath() === 'email'
                    && method_exists($error, 'getConstraint')
                    && $error->getConstraint() instanceof UniqueEntity
                ) {
                    throw new DuplicateEmailException($service);
                }
            }
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $request = $this->requestStack->getCurrentRequest();
        $eventResponse = new RedirectResponse('/');
        $event = new FormEvent($form, $request);
        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_SUCCESS,
            $event
        );

        $this->userManager->updateUser($user);

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_COMPLETED,
            new FilterUserResponseEvent(
                $user, $request,
                $eventResponse
            )
        );

        return $user;
    }

    private function getUserInfo(UserResponseInterface $response)
    {
        $fullName = explode(' ', $response->getRealName(), 2);

        $userInfo = [
            'id' => $response->getUsername(),
            'email' => $response->getEmail(),
            'username' => $response->getNickname(),
            'first_name' => $fullName[0],
            'family_name' => $fullName[1],
            'access_token' => $response->getAccessToken(),
        ];

        return $userInfo;
    }

    /**
     * @param PersonInterface $person
     * @param array $userInfo
     * @param string $service
     * @return PersonInterface
     */
    private function setUserInfo(PersonInterface $person, array $userInfo, $service)
    {
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        $setter_username = $setter.'Username';

        $person->$setter_id($userInfo['id']);
        $person->$setter_token($userInfo['access_token']);
        $person->$setter_username($userInfo['username']);

        return $person;
    }

    /**
     * @param $service
     * @param $userInfo
     * @return mixed
     * @throws MissingEmailException
     */
    private function checkEmail($service, $userInfo)
    {
        if (!$userInfo['email'] || $this->session->has("$service.email")) {
            if (!$this->session->get("$service.email")) {
                $this->session->set("$service.userinfo", $userInfo);
                throw new MissingEmailException($service);
            }
            $userInfo['email'] = $this->session->get("$service.email");
            $this->session->remove("$service.email");
            $this->session->remove("$service.userinfo");
        }

        return $userInfo;
    }

    private function setFacebookData($person, $fbdata)
    {
        if (!($person instanceof PersonInterface)) {
            return;
        }

        if (isset($fbdata['id'])) {
            $person->setFacebookId($fbdata['id']);
            $person->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['first_name']) && is_null($person->getFirstName())) {
            $person->setFirstName($fbdata['first_name']);
        }
        if (isset($fbdata['last_name']) && is_null($person->getSurname())) {
            $person->setSurname($fbdata['last_name']);
        }
        if (isset($fbdata['email']) && is_null($person->getEmail())) {
            $person->setEmail($fbdata['email']);
        }
        if (isset($fbdata['birthday']) && is_null($person->getBirthdate())) {
            $date = \DateTime::createFromFormat('m/d/Y', $fbdata['birthday']);
            $person->setBirthdate($date);
        }
        if (isset($fbdata['username']) && is_null($person->getFacebookUsername())) {
            $person->setFacebookUsername($fbdata['username']);
        }
    }
}
