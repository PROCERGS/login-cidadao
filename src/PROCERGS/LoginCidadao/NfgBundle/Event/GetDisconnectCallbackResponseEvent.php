<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Event;

use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use Symfony\Component\HttpFoundation\Response;

class GetDisconnectCallbackResponseEvent extends GetResponseEvent
{
    /** @var PersonMeuRS */
    private $personMeuRS;

    public function __construct(PersonMeuRS $personMeuRS, Response $response = null)
    {
        parent::__construct($response);
        $this->personMeuRS = $personMeuRS;
    }

    /**
     * @return PersonMeuRS
     */
    public function getPersonMeuRS()
    {
        return $this->personMeuRS;
    }
}
