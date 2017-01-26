<?php

namespace LoginCidadao\APIBundle\Event\Security;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;

class AnnotationListener
{

    /** @var Reader */
    protected $reader;

    /** @var ActionLogger */
    protected $logger;

    public function __construct(Reader $reader, ActionLogger $logger)
    {
        $this->reader = $reader;
        $this->logger = $logger;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof Loggable) {
                $this->logger->logActivity($event->getRequest(), $annotation, $controller);
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $responseCode = $response->getStatusCode();

        $annotations = $this->getLoggableAnnotations($request);
        if (is_array($annotations)) {
            foreach ($annotations as $annotation) {
                $actionLogId = $annotation->getActionLogId();
                $this->logger->updateResponseCode($actionLogId, $responseCode);
            }
        }
    }

    /**
     *
     * @param Request $request
     * @return Loggable[]
     */
    private function getLoggableAnnotations(Request $request)
    {
        return $request->attributes->get('_loggable');
    }

}
