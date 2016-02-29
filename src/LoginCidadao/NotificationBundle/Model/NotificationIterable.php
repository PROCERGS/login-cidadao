<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Handler\AuthenticatedNotificationHandlerInterface;
use LoginCidadao\NotificationBundle\Model\NotificationInterface;
use LoginCidadao\InfiniteScrollBundle\Model\AbstractInfiniteIterable;

class NotificationIterable extends AbstractInfiniteIterable
{

    const END = 'END';

    /** @var AuthenticatedNotificationHandlerInterface */
    protected $handler;
    protected $nextOffset = null;
    protected $valid = true;
    protected $cache = array();

    public function __construct(AuthenticatedNotificationHandlerInterface $handler,
                                $perIteration, $offset = 0)
    {
        parent::__construct($perIteration);
        $this->handler = $handler;
        $this->setOffset($offset);
        $this->setInitialOffset($offset);
        $this->current();
    }

    public function current()
    {
        if (array_key_exists($this->getOffset(), $this->cache)) {
            return $this->cache[$this->offset];
        }

        $current = $this->getCurrentData();
        if (is_array($current)) {
            $last = end($current);
            if ($last instanceof NotificationInterface) {
                $this->nextOffset = $last->getId();
            } else {
                $this->nextOffset = self::END;
                $this->valid = false;
            }
        } else {
            $this->nextOffset = self::END;
            $this->valid = false;
        }

        $this->cache[$this->getOffset()] = $current;

        return $current;
    }

    public function key()
    {
        return $this->getOffset();
    }

    public function next()
    {
        if ($this->nextOffset !== self::END) {
            if ($this->nextOffset === null) {
                $this->current();
            }
            $this->setOffset($this->nextOffset);
            return $this->current();
        } else {
            $this->valid = false;
        }
    }

    public function rewind()
    {
        $this->setOffset($this->getInitialOffset());
        return $this->current();
    }

    public function valid()
    {
        return $this->valid;
    }

    private function getOrder()
    {
        return array(
            'id' => 'DESC'
        );
    }

    public function getNextOffset()
    {
        return $this->nextOffset;
    }

    protected function getCurrentData()
    {
        $notifications = $this->handler->allIdOffset($this->getPerIteration(),
                                                        $this->getOffset());

        return $notifications;
    }

}
