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

use Symfony\Component\HttpFoundation\Response;

class GetLoginCallbackResponseEvent extends GetResponseEvent
{
    /** @var array */
    private $params;

    public function __construct(array $params, Response $response = null)
    {
        parent::__construct($response);
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
