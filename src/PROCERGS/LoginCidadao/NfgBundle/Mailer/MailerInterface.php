<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Mailer;

use LoginCidadao\CoreBundle\Model\PersonInterface;

interface MailerInterface
{
    public function notifyCpfLost(PersonInterface $person);
    public function notifyConnectionTransferred(PersonInterface $person);
}
