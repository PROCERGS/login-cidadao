<?php

namespace LoginCidadao\InfiniteScrollBundle\Model;

abstract class AbstractInfiniteIterable implements \Iterator
{

    protected $perIteration;
    protected $initialOffset;
    protected $offset;

    /**
     * @param integer $perIteration results per iteration
     */
    public function __construct($perIteration)
    {
        $this->perIteration = $perIteration;
    }

    public function getPerIteration()
    {
        return $this->perIteration;
    }

    public function setPerIteration($perIteration)
    {
        $this->perIteration = $perIteration;
        return $this;
    }

    public function getInitialOffset()
    {
        return $this->initialOffset;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setInitialOffset($initialOffset)
    {
        $this->initialOffset = $initialOffset;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

}
