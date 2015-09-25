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
use LoginCidadao\OpenIDBundle\Entity\Scope;
use OAuth2\ServerBundle\Manager\ScopeManagerInterface;
use OAuth2\ServerBundle\Manager\ScopeManager as BaseManager;

class ScopeManager extends BaseManager implements ScopeManagerInterface
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /**
     * Find all the scopes by an array of scopes
     *
     * @param array $scopes
     * @return mixed|void
     */
    public function findScopesByScopes(array $scopes)
    {
        $scopeObjects = $this->em->getRepository('OAuth2ServerBundle:Scope')
                ->createQueryBuilder('a')
                ->where('a.scope IN (?1)')
                ->setParameter(1, $scopes)
                ->getQuery()->getResult();

        return $scopeObjects;
    }
}
