<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ClientManager
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    public function getClientById($id)
    {
        $randomId = null;
        if (strstr($id, '_') !== false) {
            $parts = explode('_', $id);
            $id = $parts[0];
            $randomId = $parts[1];
        }

        $repo = $this->em->getRepository('LoginCidadaoOAuthBundle:Client');

        if ($randomId) {
            $client = $repo->findOneBy([
                'id' => $id,
                'randomId' => $randomId,
            ]);
        } else {
            $client = $repo->find($id);
        }
        $event = new GetClientEvent($client);
        $this->dispatcher->dispatch(LoginCidadaoCoreEvents::GET_CLIENT, $event);

        return $event->getClient();
    }
}
