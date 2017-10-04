<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle;

class NfgEvents
{
    /**
     * The LOGIN_CALLBACK_RESPONSE event occurs when the login callback finishes and has a Response ready.
     *
     * @Event("PROCERGS\LoginCidadao\NfgBundle\Event\GetLoginCallbackResponseEvent")
     */
    const LOGIN_CALLBACK_RESPONSE = 'nfg.login.callback.response';

    /**
     * The CONNECT_CALLBACK_RESPONSE event occurs when the connect callback finishes and has a Response ready.
     *
     * @Event("PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent")
     */
    const CONNECT_CALLBACK_RESPONSE = 'nfg.connect.callback.response';

    /**
     * The DISCONNECT_CALLBACK_RESPONSE event occurs when the disconnect finishes and has a Response ready.
     *
     * @Event("PROCERGS\LoginCidadao\NfgBundle\Event\GetDisconnectCallbackResponseEvent")
     */
    const DISCONNECT_CALLBACK_RESPONSE = 'nfg.disconnect.callback.response';
}
