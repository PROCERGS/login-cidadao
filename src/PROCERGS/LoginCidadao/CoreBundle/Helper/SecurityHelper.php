<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityHelper
{

    const ROLE_SUPER_ADMIN = 4;
    const ROLE_ADMIN = 3;
    const ROLE_SUPER_USER = 2;
    const ROLE_DEV = 1;
    const ROLE_USER = 0;

    /** @var SecurityContextInterface */
    private $security;

    /** @var ReflectionClass */
    private $reflection;

    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    public function getLoggedInUserLevel()
    {
        $level = 0;
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $level = self::ROLE_SUPER_ADMIN;
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            $level = self::ROLE_ADMIN;
        } elseif ($this->security->isGranted('ROLE_SUPER')) {
            $level = self::ROLE_SUPER_USER;
        } elseif ($this->security->isGranted('ROLE_DEV')) {
            $level = self::ROLE_DEV;
        } elseif ($this->security->isGranted('ROLE_USER')) {
            $level = self::ROLE_USER;
        }

        return $level;
    }

    public function getTargetPersonLevel(PersonInterface $person)
    {
        $roles = $person->getRoles();
        $level = 0;

        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            $level = self::ROLE_SUPER_ADMIN;
        } elseif (in_array('ROLE_ADMIN', $roles)) {
            $level = self::ROLE_ADMIN;
        } elseif (in_array('ROLE_SUPER', $roles)) {
            $level = self::ROLE_SUPER_USER;
        } elseif (in_array('ROLE_DEV', $roles)) {
            $level = self::ROLE_DEV;
        } elseif (in_array('ROLE_USER', $roles)) {
            $level = self::ROLE_USER;
        }

        return $level;
    }

    public function getRoleLevel($role)
    {
        return $this->getReflection()->getConstant($role);
    }

    /**
     * @return \ReflectionClass
     */
    private function getReflection()
    {
        if (!($this->reflection instanceof \ReflectionClass)) {
            $this->reflection = new \ReflectionClass(get_class());
        }
        return $this->reflection;
    }

}
