<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Event;

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\TaskStackBundle\Model\IntentTask;
use LoginCidadao\TaskStackBundle\Model\UrlTaskTarget;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    /** @var TaskStackManagerInterface */
    private $stackManager;

    /**
     * RequestSubscriber constructor.
     * @param TaskStackManagerInterface $stackManager
     */
    public function __construct(TaskStackManagerInterface $stackManager)
    {
        $this->stackManager = $stackManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            LoginCidadaoCoreEvents::AUTHENTICATION_ENTRY_POINT_START => 'onAuthenticationStart',
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $this->stackManager->processRequest($event->getRequest(), $event->getResponse());

        if ($response) {
            $event->setResponse($response);
        }
    }

    public function onAuthenticationStart(EntryPointStartEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        // Check and remove value of Symfony's redirection-after-login feature's key so that we can replace it's
        // functionality with the TaskStack system.
        $key = '_security.main.target_path'; // where "main" is your firewall name
        if ($session->has($key)) {
            $session->remove($key);
        }

        $task = new IntentTask(new UrlTaskTarget($request->getUri()));
        $this->stackManager->emptyStack();
        $this->stackManager->addNotSkippedTaskOnce($task);
    }
}
