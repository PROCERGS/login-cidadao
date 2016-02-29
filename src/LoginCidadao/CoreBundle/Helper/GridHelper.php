<?php

namespace LoginCidadao\CoreBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use LoginCidadao\InfiniteScrollBundle\Model\AbstractInfiniteIterable;
use LoginCidadao\InfiniteScrollBundle\Model\QueryBuilderIterator;

class GridHelper
{

    /** @var integer */
    protected $perPage;

    /** @var integer */
    protected $maxResult = 25;

    /** @var integer */
    protected $page;

    /** @var boolean */
    protected $infiniteGrid = false;
    protected $resultset;

    /** @var QueryBuilder */
    protected $queryBuilder;
    protected $rlength;
    protected $rstart;
    protected $rlast;
    protected $rpage;
    protected $id;

    /** @var string */
    protected $route;

    /** @var array */
    protected $routeParams;

    /** @var array */
    protected $extraOpts;

    /** @var AbstractInfiniteIterable */
    protected $iterable;

    public function __construct(AbstractInfiniteIterable $iterable = null)
    {
        if (null !== $iterable) {
            $this->setIterable($iterable);
            $this->setMaxResult($this->getIterable()->getPerIteration());
            $this->setPerPage($this->getIterable()->getPerIteration());
        }
    }

    /**
     * @param boolean $infinite
     * @return GridHelper
     */
    public function setInfiniteGrid($infinite)
    {
        $this->infiniteGrid = $infinite;
        return $this;
    }

    public function setPerPage($var)
    {
        $this->perPage = $var;
        return $this;
    }

    public function setMaxResult($var)
    {
        $this->maxResult = $var;
        return $this;
    }

    /**
     * @deprecated since version 1.1.0
     * @param QueryBuilder $var
     * @return GridHelper
     */
    public function setQueryBuilder(QueryBuilder & $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        // create QueryBuilderIterator for legacy code
        $iterable = new QueryBuilderIterator($queryBuilder, $this->getPerPage());
        $this->setIterable($iterable);
        return $this;
    }

    public function createView(Request $request)
    {
        if ($request->get('page')) {
            $this->page = $request->get('page');
            if (null !== $this->queryBuilder) {
                $this->queryBuilder->setFirstResult(($this->page - 1) * $this->maxResult);
            }
            if (null !== $this->getIterable()) {
                $this->getIterable()->setInitialOffset(($this->page - 1) * $this->maxResult);
                if (null !== $this->queryBuilder) {
                    $this->getIterable()->setOffset($this->getIterable()->getInitialOffset());
                }
            }
        } else {
            $this->page = 1;
        }
        if ($this->infiniteGrid) {
            $this->perPage = $this->maxResult;
        }
        if (null !== $this->getIterable()) {
            //$this->queryBuilder->setMaxResults($this->maxResult + 1);
            $this->resultset = $this->getIterable()->current();
            //$this->resultset = $this->queryBuilder->getQuery()->getResult();
        } else {
            $this->resultset = array();
        }
        $this->rlength = count($this->resultset);
        $this->rstart = ($this->page * $this->maxResult) / $this->perPage;
        $this->rlast = ($this->rlength < $this->maxResult);

        $this->rpage = (integer) (($this->rlength / $this->perPage) - (($this->rlength - $this->maxResult) > 0 ? 1 : 0));
        $this->rpage = $this->rpage > 0 ? $this->rpage : 0;

        if ($this->routeParams) {
            foreach ($this->routeParams as $val) {
                $a[$val] = $request->get($val);
            }
            $this->routeParams = $a;
        } else {
            $this->routeParams = array();
        }
        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getMaxResult()
    {
        return $this->maxResult;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    public function getResultset()
    {
        return $this->resultset;
    }

    public function getRlength()
    {
        return $this->rlength;
    }

    public function getRstart()
    {
        return $this->rstart;
    }

    public function getRlast()
    {
        return $this->rlast;
    }

    public function getRpage()
    {
        return $this->rpage;
    }

    public function isInfiniteGrid()
    {
        return $this->infiniteGrid;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setRoute($var)
    {
        $this->route = $var;
        return $this;
    }

    public function setRouteParams($var)
    {
        $this->routeParams = $var;
        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function setExtraOpts($var)
    {
        $this->extraOpts = $var;
        return $this;
    }

    public function getExtraOpts()
    {
        return $this->extraOpts;
    }

    /**
     * @return AbstractInfiniteIterable
     */
    public function getIterable()
    {
        return $this->iterable;
    }

    /**
     * @param AbstractInfiniteIterable $iterable
     * @return GridHelper
     */
    public function setIterable(AbstractInfiniteIterable $iterable)
    {
        $this->iterable = $iterable;
        return $this;
    }

}
