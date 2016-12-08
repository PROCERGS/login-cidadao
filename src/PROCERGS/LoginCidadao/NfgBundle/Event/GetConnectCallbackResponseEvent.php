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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetConnectCallbackResponseEvent extends GetResponseEvent
{
    /** @var Request */
    private $request;

    /** @var PersonMeuRS */
    private $personMeuRS;

    /** @var bool */
    private $overrideExisting;

    public function __construct(
        Request $request,
        PersonMeuRS $personMeuRS,
        $overrideExisting = false,
        Response $response = null
    ) {
        parent::__construct($response);
        $this->request = $request;
        $this->personMeuRS = $personMeuRS;
        $this->overrideExisting = $overrideExisting;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return PersonMeuRS
     */
    public function getPersonMeuRS()
    {
        return $this->personMeuRS;
    }

    /**
     * @return boolean
     */
    public function isOverrideExisting()
    {
        return $this->overrideExisting;
    }
}
