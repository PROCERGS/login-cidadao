<?php

namespace LoginCidadao\CoreBundle\Helper;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityHelper
{
    /** @var AuthorizationCheckerInterface */
    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    public function getLoggedInUserLevel()
    {
        $level = 0;
        foreach ($this->getRoleMapping() as $role => $lvl) {
            if ($this->security->isGranted($role)) {
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
}
