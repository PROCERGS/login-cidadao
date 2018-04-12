<?php

namespace LoginCidadao\CoreBundle\Helper;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\RouterInterface;
use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

class SecurityHelper
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ActionLogRepository */
    private $actionLogRepo;

    /** @var ExtremeNotificationsHelper */
    private $extremeNotificationsHelper;

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $cookieRememberMeName;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        ActionLogRepository $actionLogRepo,
        ExtremeNotificationsHelper $extremeNotificationsHelper,
        RouterInterface $router,
        $cookieRememberMeName
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->actionLogRepo = $actionLogRepo;
        $this->extremeNotificationsHelper = $extremeNotificationsHelper;
        $this->router = $router;
        $this->cookieRememberMeName = $cookieRememberMeName;
    }

    public function getLoggedInUserLevel()
    {
        foreach ($this->getRoleMapping() as $role => $lvl) {
            if ($this->authChecker->isGranted($role)) {
                return $lvl;
            }
        }

        return 0;
    }

    public function getTargetPersonLevel(PersonInterface $person)
    {
        $roles = $person->getRoles();
        foreach ($this->getRoleMapping() as $role => $lvl) {
            if (in_array($role, $roles)) {
                return $lvl;
            }
        }

        return 0;
    }

    public function getRoleLevel($role)
    {
        $map = $this->getRoleMapping();
        if (array_key_exists($role, $map)) {
            return $map[$role];
        } else {
            return max(array_values($map));
        }
    }

    /**
     * @return bool
     */
    public function hasToken()
    {
        return $this->tokenStorage->getToken() !== null;
    }

    /**
     * @return bool
     */
    public function isOAuthToken()
    {
        return $this->tokenStorage->getToken() instanceof OAuthToken;
    }

    private function getRoleMapping()
    {
        $map = [
            'ROLE_SUPER_ADMIN' => 4,
            'ROLE_ADMIN' => 3,
            'ROLE_SUPER_USER' => 2,
            'ROLE_DEV' => 1,
            'ROLE_USER' => 0,
        ];
        arsort($map);

        return $map;
    }

    public function checkPendingImpersonateReport(PersonInterface $impersonator)
    {
        $count = $this->actionLogRepo->countImpersonatonsWithoutReports($impersonator);

        if ($count <= 0) {
            return;
        }

        $url = $this->router->generate('lc_admin_impersonation_report_index');

        $parameters = ['%url%' => $url, '%count%' => $count];
        $message = 'admin.impersonation_report.pending.notification';
        $this->extremeNotificationsHelper
            ->addTransChoice($message, $count, $parameters);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function logout(Request $request, Response $response)
    {
        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $cookieNames = [$this->cookieRememberMeName];
        foreach ($cookieNames as $cookieName) {
            $response->headers->clearCookie($cookieName);
        }

        return $response;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->authChecker->isGranted($attributes, $object);
    }

    /**
     * @return PersonInterface|null
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        try {
            /** @var PersonInterface $user */
            $user = $token->getUser();

            if (!$user instanceof PersonInterface) {
                $user = null;
            }
        } catch (\Exception $e) {
            $user = null;
        }

        return $user;
    }

    /**
     * @return RoleInterface[]
     */
    public function getTokenRoles()
    {
        return $this->tokenStorage->getToken()->getRoles();
    }
}
