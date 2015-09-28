<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionState
{
    /** @var EntityManager */
    protected $em;

    /** @var SessionInterface */
    protected $session;

    public function __construct(EntityManager $em, SessionInterface $session)
    {
        $this->em      = $em;
        $this->session = $session;
    }

    public function getSessionState($client_id, $sessionId)
    {
        $generator = new SecureRandom();
        $client    = $this->getClient($client_id);

        $url  = $client->getMetadata()->getClientUri();
        $salt = bin2hex($generator->nextBytes(15));

        $state = $client_id.$url.$sessionId.$salt;

        return hash('sha256', $state).".$salt";
    }

    public function getSessionId()
    {
        return $this->session->getId();
    }

    /**
     * @param string $client_id
     * @return \PROCERGS\OAuthBundle\Entity\Client
     */
    private function getClient($client_id)
    {
        $id = explode('_', $client_id);
        return $this->em->getRepository('PROCERGSOAuthBundle:Client')->find($id[0]);
    }
}
