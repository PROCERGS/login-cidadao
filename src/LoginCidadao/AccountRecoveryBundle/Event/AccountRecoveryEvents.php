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

class AccountRecoveryEvents
{
    const ACCOUNT_RECOVERY_DATA_EDIT_INITIALIZE = 'account_recovery_data.edit.initialize';
    const ACCOUNT_RECOVERY_DATA_EDIT_SUCCESS = 'account_recovery_data.edit.success';
    const ACCOUNT_RECOVERY_DATA_EDIT_COMPLETED = 'account_recovery_data.edit.completed';

    const ACCOUNT_RECOVERY_RESET_PASSWORD_SEND_SMS = 'account_recovery.reset_password.sms.send';
    const ACCOUNT_RECOVERY_RESET_PASSWORD_SMS_SENT = 'account_recovery.reset_password.sms.sent';
}
