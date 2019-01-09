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

        /** @var PersonInterface|null $existingUser */
        $existingUser = $this->userManager->findUserBy(["{$service}Id" => $username]);
        if ($existingUser instanceof UserInterface && $existingUser->getId() != $user->getId()) {
            throw new AlreadyLinkedAccount();
        }

        $user = $this->setServiceData($user, $response);

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

        $user = $this->userManager->findUserBy(["{$service}Id" => $userInfo['id']]);

        if ($user instanceof PersonInterface) {
            $this->setAccessToken($user, $service, $response->getAccessToken());

            return $user;
        }

        return $this->createOAuthUser($userInfo, $service, $response->getData());
    }

    /**
     * @param array $userInfo
     * @param string $service
     * @param array $oauthData
     * @return PersonInterface
     * @throws DuplicateEmailException
     * @throws MissingEmailException
     */
    private function createOAuthUser(array $userInfo, string $service, array $oauthData)
    {
        $userInfo = $this->checkEmail($service, $userInfo);

        /** @var PersonInterface $user */
        $user = $this->userManager->createUser();
        $this->setUserInfo($user, $userInfo, $service);

        if ($service === 'facebook') {
            $this->setFacebookData($user, $oauthData);
        }

        $username = Uuid::uuid4()->toString();

        $user->setUsername($username);
        $user->setEmail($userInfo['email']);
        $user->setPassword('');
        $user->setEnabled(true);
        $this->userManager->updateCanonicalFields($user);

        $this->checkErrors($user, $service);

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $request = $this->requestStack->getCurrentRequest();
        $eventResponse = new RedirectResponse('/');
        $event = new FormEvent($form, $request);
        $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

        $this->userManager->updateUser($user);

        $event = new FilterUserResponseEvent($user, $request, $eventResponse);
        $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, $event);

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
    private function setUserInfo(PersonInterface $person, array $userInfo, string $service)
    {
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_username = $setter.'Username';

        $person->$setter_id($userInfo['id']);
        $this->setAccessToken($person, $service, $userInfo['access_token']);
        $person->$setter_username($userInfo['username']);

        if ($userInfo['first_name']) {
            $person->setFirstName($userInfo['first_name']);
        }
        if ($userInfo['family_name']) {
            $person->setSurname($userInfo['family_name']);
        }

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
        $emailKey = "{$service}.email";
        $userInfoKey = "{$service}.userinfo";
        if (!$userInfo['email'] || $this->session->has($emailKey)) {
            if (!$this->session->get($emailKey)) {
                $this->session->set($userInfoKey, $userInfo);
                throw new MissingEmailException($service);
            }
            $userInfo['email'] = $this->session->get($emailKey);
            $this->session->remove($emailKey);
            $this->session->remove($userInfoKey);
        }

        return $userInfo;
    }

    private function setFacebookData($person, array $data)
    {
        if ($person instanceof PersonInterface) {
            if (isset($data['id'])) {
                $person->setFacebookId($data['id']);
                $person->addRole('ROLE_FACEBOOK');
            }
            if (isset($data['first_name']) && is_null($person->getFirstName())) {
                $person->setFirstName($data['first_name']);
            }
            if (isset($data['last_name']) && is_null($person->getSurname())) {
                $person->setSurname($data['last_name']);
            }
            if (isset($data['email']) && is_null($person->getEmail())) {
                $person->setEmail($data['email']);
            }
            if (isset($data['birthday']) && is_null($person->getBirthdate())) {
                $date = \DateTime::createFromFormat('m/d/Y', $data['birthday']);
                $person->setBirthdate($date);
            }
            if (isset($data['username']) && is_null($person->getFacebookUsername())) {
                $person->setFacebookUsername($data['username']);
            }
        }
    }

    private function setServiceData(UserInterface $user, UserResponseInterface $response): UserInterface
    {
        if ($user instanceof PersonInterface) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_username = $setter.'Username';

            $user->$setter_id($response->getUsername());
            $this->setAccessToken($user, $service, $response->getAccessToken());
            $user->$setter_username($response->getNickname());
        }

        return $user;
    }

    private function setAccessToken(UserInterface $user, string $serviceName, string $accessToken)
    {
        $setter = 'set'.ucfirst($serviceName).'AccessToken';

        return $user->$setter($accessToken);
    }

    /**
     * @param UserInterface $user
     * @param string $service
     * @throws DuplicateEmailException
     */
    private function checkErrors(UserInterface $user, string $service)
    {
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
    }
}
