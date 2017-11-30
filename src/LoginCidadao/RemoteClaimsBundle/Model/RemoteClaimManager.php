<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository;

class RemoteClaimManager implements RemoteClaimManagerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var RemoteClaimAuthorizationRepository */
    private $remoteClaimAuthorizationRepository;

    /**
     * RemoteClaimManager constructor.
     * @param EntityManagerInterface $em
     * @param RemoteClaimAuthorizationRepository $remoteClaimAuthorizationRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        RemoteClaimAuthorizationRepository $remoteClaimAuthorizationRepository
    ) {
        $this->em = $em;
        $this->remoteClaimAuthorizationRepository = $remoteClaimAuthorizationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function enforceAuthorization(RemoteClaimAuthorizationInterface $authorization)
    {
        $existingAuthorization = $this->remoteClaimAuthorizationRepository->findAuthorization($authorization);
        if ($existingAuthorization instanceof RemoteClaimAuthorizationInterface) {
            return $existingAuthorization;
        }

        $this->em->persist($authorization);

        return $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized($claimName, PersonInterface $person, ClientInterface $client)
    {
        if (!$claimName instanceof TagUri) {
            $claimName = TagUri::createFromString($claimName);
        }
        $authorization = (new RemoteClaimAuthorization())
            ->setClaimName($claimName)
            ->setPerson($person)
            ->setClient($client);
        $existingAuthorization = $this->remoteClaimAuthorizationRepository->findAuthorization($authorization);

        return $existingAuthorization instanceof RemoteClaimAuthorizationInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAllAuthorizations(Authorization $authorization)
    {
        $remoteClaimAuthorizations = $this->remoteClaimAuthorizationRepository
            ->findAllByClientAndPerson($authorization->getClient(), $authorization->getPerson());

        foreach ($remoteClaimAuthorizations as $remoteClaimAuthorization) {
            $this->em->remove($remoteClaimAuthorization);
        }
        $this->em->flush();

        return true;
    }
}
