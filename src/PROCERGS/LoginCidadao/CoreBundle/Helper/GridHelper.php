<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\Tests\Fixtures\Publisher;

class GridHelper
{

    protected $perPage;

    protected $maxResult = 25;

    protected $page;

    protected $infinityGrid = false;

    protected $resultset;

    protected $queryBuilder;

    protected $rlength;

    protected $rstart;

    protected $rlast;

    protected $rpage;

    protected $id;
    protected $route;
    protected $routeParams;

    public function setInfinityGrid($var)
    {
        $this->infinityGrid = $var;
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

    public function setQueryBuilder(QueryBuilder &$var)
    {
        $this->queryBuilder = $var;
        return $this;
    }

    public function createView(Request &$request)
    {
        if ($request->get('page')) {
            $this->page = $request->get('page');
            $this->queryBuilder->setFirstResult($this->page * $this->maxResult);
        } else {
            $this->page = 0;
        }
        if ($this->infinityGrid) {
            $this->perPage = $this->maxResult;
        }
        $this->queryBuilder->setMaxResults($this->maxResult + 1);
        $this->resultset = $this->queryBuilder->getQuery()->getResult();
        
        $this->rlength = count($this->resultset);
        $this->rstart = ($this->page * $this->maxResult) / $this->perPage;
        $this->rlast = ($this->rlength - $this->maxResult) > 0;
        if ($this->rlast) {
            array_pop($this->resultset);
        }
        $this->rpage = (integer) (($this->rlength / $this->perPage) - (($this->rlength - $this->maxResult) > 0 ? 1 : 0));
        $this->rpage = $this->rpage > 0 ? $this->rpage : 0;
        
        foreach ($this->routeParams as $val) {
            $a[$val] = $request->get($val);
        }
        $this->routeParams = $a;
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

    public function isInfinityGrid()
    {
        return $this->infinityGrid;
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
    
}