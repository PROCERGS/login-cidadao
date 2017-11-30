<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Egulias\EmailValidator\EmailValidator;

class PersonRepository extends EntityRepository
{

    public function findAllPendingCPF()
    {
        return $this->getEntityManager()
                ->createQuery('SELECT p FROM LoginCidadaoCoreBundle:Person p WHERE p.cpf IS NULL')
                ->getResult();
    }

    public function findUnconfirmedEmailUntilDate(\DateTime $dateLimit)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM LoginCidadaoCoreBundle:Person p WHERE p.emailConfirmedAt IS NULL AND p.emailExpiration <= :date'
            )
                ->setParameter('date', $dateLimit)
                ->getResult();
    }

    public function getFindAuthorizedByClientIdQuery($clientId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(
                'LoginCidadaoCoreBundle:Authorization',
                'a',
                'WITH',
                'a.person = p'
            )
            ->innerJoin(
                'LoginCidadaoOAuthBundle:Client',
                'c',
                'WITH',
                'a.client = c'
            )
                ->andWhere('c.id = :clientId')
            ->setParameter('clientId', $clientId);
    }

    public function getCountByCountry()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('count(p.country) AS qty, c.name')
                ->from('LoginCidadaoCoreBundle:Person', 'p')
            ->innerJoin(
                'LoginCidadaoCoreBundle:Country',
                'c',
                'WITH',
                'p.country = c'
            )
                ->where('p.country IS NOT NULL')
                ->groupBy('p.country, c.name')
                ->orderBy('qty', 'DESC')
                ->getQuery()->getResult();
    }

    public function getCountByState()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('count(p.state) AS qty, s.id, s.name, c.name AS country')
                ->from('LoginCidadaoCoreBundle:Person', 'p')
            ->innerJoin(
                'LoginCidadaoCoreBundle:State',
                's',
                'WITH',
                'p.state = s'
            )
            ->innerJoin(
                'LoginCidadaoCoreBundle:Country',
                'c',
                'WITH',
                's.country = c'
            )
                ->where('p.state IS NOT NULL')
                ->groupBy('p.state, s.name, country, s.id')
                ->orderBy('qty', 'DESC')
                ->getQuery()->getResult();
    }

    public function getCountByCity($stateId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('count(p.city) AS qty, c.name')
                ->from('LoginCidadaoCoreBundle:Person', 'p')
            ->innerJoin(
                'LoginCidadaoCoreBundle:City',
                'c',
                'WITH',
                'p.city = c'
            )
            ->innerJoin(
                'LoginCidadaoCoreBundle:State',
                's',
                'WITH',
                'c.state = s'
            )
                ->where('p.city IS NOT NULL')
                ->andWhere('s.id = :stateId')
                ->groupBy('p.city, c.name')
                ->orderBy('qty', 'DESC')
                ->setParameter('stateId', $stateId)
                ->getQuery()->getResult();
    }

    public function getCountAll()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('count(p.id) AS qty')
                ->from('LoginCidadaoCoreBundle:Person', 'p')
                ->orderBy('qty', 'DESC')
                ->getQuery()->getSingleResult();
    }

    public function getUserSearchQuery($query)
    {
        return $this->createQueryBuilder('p')
            ->where(
                'p.cpf LIKE :query OR p.username LIKE :query OR p.email LIKE :query OR p.firstName LIKE :query OR p.surname LIKE :query'
            )
                ->setParameter('query', '%'.addcslashes($query, '\\%_').'%')
                ->addOrderBy('p.id', 'DESC');
    }

    public function getFindByIdIn($ids)
    {
        return $this->createQueryBuilder('p')
                ->where('p.id in(:ids)')
                ->setParameters(compact('ids'))
                ->addOrderBy('p.id', 'desc');
    }

    public function findOneByEmail($email)
    {
        return $this->createQueryBuilder('p')
            ->where('p.email = :email')
            ->orWhere('p.emailCanonical = :email')
            ->setParameter('email', $email)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseSearchQuery()
    {
        return $this->createQueryBuilder('p')
            ->addOrderBy('p.id', 'DESC');
    }

    public function getCpfSearchQuery($cpf)
    {
        return $this->getBaseSearchQuery()
            ->where('p.cpf = :cpf')
            ->setParameter('cpf', $cpf);
    }

    public function getEmailSearchQuery($email)
    {
        return $this->getBaseSearchQuery()
            ->where('p.emailCanonical = LOWER(:email)')
            ->setParameter('email', $email);
    }

    public function getNameSearchQuery($name)
    {
        $sanitized = addcslashes($name, "%_");

        return $this->getBaseSearchQuery()
            ->where('LowerUnaccent(p.username) LIKE LowerUnaccent(:name)')
            ->orWhere('LowerUnaccent(p.firstName) LIKE LowerUnaccent(:name)')
            ->orWhere('LowerUnaccent(p.surname) LIKE LowerUnaccent(:name)')
            ->setParameter('name', "%{$sanitized}%");
    }

    /**
     * This will return the appropriate query for the input given.
     * @param $query
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSmartSearchQuery($query)
    {
        $query = trim($query);

        if (strlen($query) == 0) {
            return $this->getBaseSearchQuery();
        }

        $cpfRegex = '/^(?:\d{3}\.?){2}\d{3}-?\d{2}$/';
        // Check CPF
        if (preg_match($cpfRegex, $query) === 1) {
            $cpf = str_replace(['.', '-'], '', $query);

            return $this->getCpfSearchQuery($cpf);
        }

        // Check email
        $emailValidator = new EmailValidator();
        if ($emailValidator->isValid($query)) {
            return $this->getEmailSearchQuery($query);
        }

        // Defaults to name search
        return $this->getNameSearchQuery($query);
    }
}
