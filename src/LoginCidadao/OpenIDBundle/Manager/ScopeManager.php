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
     * Creates a new scope
     *
     * @param string $scope
     *
     * @param string $description
     *
     * @param string $name
     *
     * @return Scope
     */
    public function createScope($scope, $description = null, $name = null)
    {
        $scopeObject = new Scope();
        $scopeObject->setName($name);
        $scopeObject->setScope($scope);
        $scopeObject->setDescription($description);

        // Store Scope
        $this->em->persist($scopeObject);
        $this->em->flush();

        return $scopeObject;
    }

    /**
     * Find a single scope by the scope
     *
     * @param $scope
     * @return Scope
     */
    public function findScopeByScope($scope)
    {
        $scopeObject = $this->em->getRepository('LoginCidadaoOpenIDBundle:Scope')->find($scope);

        return $scopeObject;
    }

    /**
     * Find all the scopes by an array of scopes
     *
     * @param array $scopes
     * @return mixed|void
     */
    public function findScopesByScopes(array $scopes)
    {
        $scopeObjects = $this->em->getRepository('LoginCidadaoOpenIDBundle:Scope')
                ->createQueryBuilder('a')
                ->where('a.scope in (?1)')
                ->setParameter(1, $scopes)
                ->getQuery()->getResult();

        return $scopeObjects;
    }
}
