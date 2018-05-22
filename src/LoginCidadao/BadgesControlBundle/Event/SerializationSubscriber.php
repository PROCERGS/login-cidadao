<?php

namespace LoginCidadao\BadgesControlBundle\Event;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\GenericSerializationVisitor;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;

class SerializationSubscriber implements EventSubscriberInterface
{
    /** @var BadgesHandler */
    protected $handler;

    /** @var VersionService */
    private $versionService;

    public function __construct(BadgesHandler $handler, VersionService $versionService)
    {
        $this->handler = $handler;
        $this->versionService = $versionService;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if ($person instanceof PersonInterface) {
            $this->handler->evaluate($person);
        }
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $person = $event->getObject();

        if (!$person instanceof PersonInterface) {
            return;
        }

        if (version_compare($this->getApiVersion(), '2', '>=')) {
            return;
        }

        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $badges = [];
        foreach ($person->getBadges() as $badge) {
            $key = "{$badge->getNamespace()}.{$badge->getName()}";
            $badges[$key] = $badge->getData();
        }
        $visitor->addData('badges', $badges);
    }

    /**
     * @return string
     */
    private function getApiVersion()
    {
        $version = $this->versionService->getVersionFromRequest();

        return $this->versionService->getString($version);
    }
}
