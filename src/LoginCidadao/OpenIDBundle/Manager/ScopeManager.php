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

use Doctrine\ORM\EntityManager;
use LoginCidadao\OpenIDBundle\Entity\Scope;
use OAuth2\ServerBundle\Manager\ScopeManagerInterface;
use OAuth2\ServerBundle\Manager\ScopeManager as BaseManager;

class ScopeManager extends BaseManager implements ScopeManagerInterface
{
    private $em;

    /** @var \OAuth2\ServerBundle\Entity\Scope[] */
    private $scopes = array();

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    public function setScopes($scopes)
    {
        if (!is_array($scopes)) {
            $scopes = explode(' ', $scopes);
        }

        $this->scopes = array();
        foreach ($scopes as $scope) {
            $scopeObj = new \OAuth2\ServerBundle\Entity\Scope();
            $scopeObj->setScope($scope);
            $scopeObj->setDescription($scope);

            $this->scopes[$scope] = $scopeObj;
        }
    }

    /**
     * Find a single scope by the scope
     *
     * @param $scope
     * @return Scope
     */
    public function findScopeByScope($scope)
    {
        return $this->scopes[$scope];
    }

    /**
     * Find all the scopes by an array of scopes
     *
     * @param array $scopes
     * @return mixed|void
     */
    public function findScopesByScopes(array $scopes)
    {
        $result = array();
        foreach ($this->scopes as $scope => $obj) {
            if (array_search($scope, $scopes) !== false) {
                $result[$scope] = $obj;
            }
        }
        return $result;
    }
}
