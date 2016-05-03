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

use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Component\EventDispatcher\Event;

class GetClientEvent extends Event
{
    /** @var ClientInterface */
    protected $client;

    /**
     * GetClientEvent constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
