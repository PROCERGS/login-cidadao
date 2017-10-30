<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\ClientManager as FOSClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

class ClientManager extends FOSClientManager
{

    public function updateClient(ClientInterface $client)
    {
        $this->em->persist($client);
        $this->em->flush();
    }
}
