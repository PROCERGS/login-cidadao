<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Manager;

use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ClientManager
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var EntityManager */
    private $em;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    public function getClientById($id)
    {
        if (strstr($id, '_') !== false) {
            $parts = explode('_', $id);
            $id = $parts[0];
        }

        $repo = $this->em->getRepository('LoginCidadaoOAuthBundle:Client');

        $client = $repo->find($id);
        $event = new GetClientEvent($client);
        $this->dispatcher->dispatch(LoginCidadaoCoreEvents::GET_CLIENT, $event);

        return $event->getClient();
    }
}
