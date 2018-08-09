<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Event;

use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use Symfony\Component\EventDispatcher\Event;

class SendResetPasswordSmsEvent extends Event
{
    /** @var AccountRecoveryData */
    private $accountRecoveryData;

    /**
     * SendResetPasswordSmsEvent constructor.
     * @param AccountRecoveryData $accountRecoveryData
     */
    public function __construct(AccountRecoveryData $accountRecoveryData)
    {
        $this->accountRecoveryData = $accountRecoveryData;
    }

    /**
     * @return AccountRecoveryData
     */
    public function getAccountRecoveryData(): AccountRecoveryData
    {
        return $this->accountRecoveryData;
    }
}
