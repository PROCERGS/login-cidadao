<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Service;

use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;

interface NfgSoapInterface
{
    /**
     * @return string
     */
    public function getAccessID();

    /**
     * @return NfgProfile
     */
    public function getUserInfo($accessToken, $voterRegistration = null);
}
