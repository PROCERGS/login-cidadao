<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

class AuthorizationManager
{
    /** @var string merge new scope with old one */
    public const SCOPE_MERGE = 'merge';

    /** @var string replace old scope by new one */
    public const SCOPE_REPLACE = 'replace';

    /** @var EntityManagerInterface */
    private $em;

    /** @var AuthorizationRepository */
    private $repository;

    /**
     * AuthorizationManager constructor.
     * @param EntityManagerInterface $em
     * @param AuthorizationRepository $repository
     */
    public function __construct(EntityManagerInterface $em, AuthorizationRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @return Authorization|null
     */
    public function getAuthorization(PersonInterface $person, ClientInterface $client): ?Authorization
    {
        /** @var Authorization $authorization */
        $authorization = $this->repository->findOneBy([
            'person' => $person,
            'client' => $client,
        ]);

        return $authorization;
    }

    /**
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @param array $scope
     * @param string $scopeStrategy
     * @param bool $flush
     * @return Authorization
     */
    public function enforceAuthorization(
        PersonInterface $person,
        ClientInterface $client,
        array $scope,
        string $scopeStrategy,
        bool $flush = true
    ): Authorization {
        $authorization = $this->getAuthorization($person, $client);
        if (null === $authorization) {
            $authorization = (new Authorization())
                ->setPerson($person)
                ->setClient($client);
            $this->em->persist($authorization);
        }

        $authorization->setScope(
            $this->mergeScope($authorization->getScope(), $scope, $scopeStrategy)
        );

        if ($flush) {
            $this->em->flush();
        }

        return $authorization;
    }

    private function mergeScope(array $oldScope, array $newScope, string $strategy)
    {
        if (self::SCOPE_REPLACE === $strategy) {
            return $newScope;
        } elseif (self::SCOPE_MERGE === $strategy) {
            return array_unique(array_merge($oldScope, $newScope));
        } else {
            throw new \InvalidArgumentException("Invalid scope merging strategy: '{$strategy}'");
        }
    }
}
