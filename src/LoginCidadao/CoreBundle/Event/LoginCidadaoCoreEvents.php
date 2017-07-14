<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Event;

class LoginCidadaoCoreEvents
{
    /**
     * Receives a GetClientEvent
     */
    const GET_CLIENT = 'login_cidadao.core.get_client';

    /**
     * This gets triggered from the start() method of the entry_point service.
     */
    const AUTHENTICATION_ENTRY_POINT_START = 'authentication.entry_point.start';
}
