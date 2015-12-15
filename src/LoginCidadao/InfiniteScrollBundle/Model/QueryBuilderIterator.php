<?php

namespace LoginCidadao\InfiniteScrollBundle\Model;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderIterator extends AbstractInfiniteIterable
{

    /** @var QueryBuilder */
    protected $queryBuilder;

    /** @var boolean */
    protected $last = false;

    public function __construct(QueryBuilder $queryBuilder, $perIteration,
                                $offset = 0)
    {
        parent::__construct($perIteration);
        $this->setQueryBuilder($queryBuilder);
        $this->setOffset($offset);
        $this->setInitialOffset($offset);
    }

    public function current()
    {
        $query = $this->getQueryBuilder()
            ->setFirstResult($this->getOffset())
            ->setMaxResults($this->getPerIteration() + 1)
        ;

        $results = $query->getQuery()->getResult();

        if (count($results) <= $this->getPerIteration()) {
            $this->last = true;
        } else {
            array_pop($results);
        }

        return $results;
    }

    public function key()
    {
        return $this->getOffset();
    }

    public function next()
    {
        $current = $this->getOffset();
        $this->setOffset($current + $this->getPerIteration());
    }

    public function rewind()
    {
        $this->setOffset($this->getInitialOffset());
        return $this->current();
    }

    public function valid()
    {
        return !$this->last;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilderIterator
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

}
