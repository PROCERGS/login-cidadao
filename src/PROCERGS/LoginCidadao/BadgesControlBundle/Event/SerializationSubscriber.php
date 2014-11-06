<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Event;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;

class SerializationSubscriber implements EventSubscriberInterface
{

    /** @var BadgesHandler */
    protected $handler;

    public function __construct(BadgesHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface'
            )
        );
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if ($person instanceof PersonInterface) {
            $this->handler->evaluate($person);
        }
    }

}
