<?php

namespace LoginCidadao\CoreBundle\Helper;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\RouterInterface;
use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class SecurityHelper
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var ActionLogRepository */
    private $actionLogRepo;

    /** @var ExtremeNotificationsHelper */
    private $extremeNotificationsHelper;

    /** @var RouterInterface */
    private $router;

    public function __construct(AuthorizationCheckerInterface $authChecker,
                                ActionLogRepository $actionLogRepo,
                                ExtremeNotificationsHelper $extremeNotificationsHelper,
                                RouterInterface $router)
    {
        $this->authChecker                = $authChecker;
        $this->actionLogRepo              = $actionLogRepo;
        $this->extremeNotificationsHelper = $extremeNotificationsHelper;
        $this->router                     = $router;
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
        $message    = 'admin.impersonation_report.pending.notification';
        $this->extremeNotificationsHelper
            ->addTransChoice($message, $count, $parameters);
    }
}
