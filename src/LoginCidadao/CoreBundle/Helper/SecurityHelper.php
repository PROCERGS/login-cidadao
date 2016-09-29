<?php

namespace LoginCidadao\CoreBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\RouterInterface;
use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class SecurityHelper
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorage */
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
        TokenStorage $tokenStorage,
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
        $level = 0;
        foreach ($this->getRoleMapping() as $role => $lvl) {
            if ($this->authChecker->isGranted($role)) {
                $level = $lvl;
                break;
            }
        }

        return $level;
    }

    public function getTargetPersonLevel(PersonInterface $person)
    {
        $roles = $person->getRoles();
        $level = 0;
        foreach ($this->getRoleMapping() as $role => $lvl) {
            if (in_array($role, $roles)) {
                $level = $lvl;
                break;
            }
        }

        return $level;
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

    private function getRoleMapping()
    {
        $map = array(
            'ROLE_SUPER_ADMIN' => 4,
            'ROLE_ADMIN' => 3,
            'ROLE_SUPER_USER' => 2,
            'ROLE_DEV' => 1,
            'ROLE_USER' => 0,
        );
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

        $parameters = array('%url%' => $url, '%count%' => $count);
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

        $cookieNames = [
            $this->cookieRememberMeName,
        ];
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
}
