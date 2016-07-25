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

class MigratePasswordEncoderTask extends Task
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'lc.force_password_change';
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return ['fos_user_change_password', []];
    }

    public function getTaskRoutes()
    {
        return [
            'fos_user_change_password',
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
        return 50;
    }
}
