<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Model;

interface SmsStatusInterface
{
    const WAITING_TO_BE_SENT = 'WAITING_TO_BE_SENT';
    const ERROR_IN_SEND = 'ERROR_IN_SEND';
    const WAITING_TO_BE_DELIVERED = 'WAITING_TO_BE_DELIVERED';
    const DELIVERED = 'DELIVERED';
    const ERROR_IN_DELIVERY = 'ERROR_IN_DELIVERY';
    const NO_DELIVERY_CONFIRMATION = 'NO_DELIVERY_CONFIRMATION';
    const NOT_SEND = 'NOT_SEND';

    /**
     * @return \DateTime|null
     */
    public function getDateSent();

    /**
     * @return \DateTime|null
     */
    public function getDateDelivered();

    /**
     * @return string
     */
    public function getStatusCode();

    /**
     * @return string|null
     */
    public function getStatusDetails();

    /**
     * @return bool true if the current status is final
     */
    public function isFinal();
}
