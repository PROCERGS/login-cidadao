<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\OAuthBundle\Model\AccessTokenManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

class PersonSerializeEventListenner implements EventSubscriberInterface
{
    protected $templateHelper;

    /** @var UploaderHelper */
    protected $uploaderHelper;

    /** @var Kernel */
    protected $kernel;

    /** @var Request */
    protected $request;

    public function __construct(UploaderHelper $uploaderHelper, $templateHelper,
                                Kernel $kernel, Request $request)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->templateHelper = $templateHelper;
        $this->kernel         = $kernel;
        $this->request        = $request;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface'
            ),
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface'
            )
        );
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if ($person instanceof PersonInterface) {
            $imgHelper      = $this->uploaderHelper;
            $templateHelper = $this->templateHelper;
            $isDev          = $this->kernel->getEnvironment() === 'dev';
            $person->prepareAPISerialize($imgHelper, $templateHelper, $isDev,
                $this->request);
        }
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        //
    }
}
