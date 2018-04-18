<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\EventListener;

use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTaskValidator;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\Model\TaskInterface;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var SecurityHelper */
    private $securityHelper;

    /** @var ClientManager */
    private $clientManager;

    /** @var CompleteUserInfoTaskValidator */
    private $taskValidator;

    /**
     * TaskSubscriber constructor.
     * @param SecurityHelper $securityHelper
     * @param ClientManager $clientManager
     * @param CompleteUserInfoTaskValidator $taskValidator
     */
    public function __construct(
        SecurityHelper $securityHelper,
        ClientManager $clientManager,
        CompleteUserInfoTaskValidator $taskValidator
    ) {
        $this->securityHelper = $securityHelper;
        $this->clientManager = $clientManager;
        $this->taskValidator = $taskValidator;
    }

    public static function getSubscribedEvents()
    {
        return [
            TaskStackEvents::GET_TASKS => ['onGetTasks', 50],
        ];
    }

    public function onGetTasks(GetTasksEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->securityHelper->getUser();
        $client = $this->getClientFromRequest($request);

        if (!$user instanceof PersonInterface || !$client instanceof ClientInterface) {
            return;
        }

        $task = $this->taskValidator->getCompleteUserInfoTask($user, $client, $request);
        if ($task instanceof TaskInterface) {
            $event->addTask($task);
        }
    }

    private function getClientFromRequest(Request $request)
    {
        $clientId = $request->get('client_id', $request->get('clientId'));

        return $this->clientManager->getClientById($clientId);
    }
}
