<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Model;

class InvalidateSessionTask extends Task
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'lc.invalidate_session';
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return ['fos_user_security_logout', []];
    }

    public function getTaskRoutes()
    {
        return [
            'fos_user_security_logout',
        ];
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return true;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        // Top priority since it's a security Task
        return PHP_INT_MAX;
    }
}
