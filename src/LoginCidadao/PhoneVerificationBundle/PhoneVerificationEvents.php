<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle;

class PhoneVerificationEvents
{
    /**
     * This event is triggered AFTER the user changes the phone number in the profile.
     */
    const PHONE_CHANGED = 'lc.phone_verification.phone_changed';

    /**
     * This event is triggered AFTER the user's phone gets verified.
     */
    const PHONE_VERIFIED = 'lc.phone_verification.phone_verified';

    /**
     * This event is triggered when there is a need to verify the user's phone.
     *
     * This can trigger a service to send SMS or to call the user's phone.
     */
    const PHONE_VERIFICATION_REQUESTED = 'lc.phone_verification.requested';

    /**
     * This event is triggered after the verification code is sent.
     */
    const PHONE_VERIFICATION_CODE_SENT = 'lc.phone_verification.code_sent';
}
