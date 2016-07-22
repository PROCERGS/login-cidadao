<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use LoginCidadao\CoreBundle\Event\GetTasksEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\Task;
use LoginCidadao\CoreBundle\Service\IntentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var IntentManager */
    protected $intentManager;

    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var array */
    protected $options;

    /**
     * TaskSubscriber constructor.
     * @param IntentManager $intentManager
     * @param TokenStorage $tokenStorage
     * @param bool $mandatoryEmailValidation
     */
    public function __construct(IntentManager $intentManager, TokenStorage $tokenStorage, $mandatoryEmailValidation)
    {
        $this->intentManager = $intentManager;
        $this->tokenStorage = $tokenStorage;
        $this->options['mandatoryEmailValidation'] = $mandatoryEmailValidation;
    }


    public static function getSubscribedEvents()
    {
        return array(
            LoginCidadaoCoreEvents::GET_TASKS => array('onGetTasks', 0),
        );
    }

    public function onGetTasks(GetTasksEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!($user instanceof PersonInterface)) {
            return;
        }

        if ($this->options['mandatoryEmailValidation'] && $user->getConfirmationToken()) {
            $task = new Task();
            $task->setTarget('')
                ->setIsMandatory(true)
                ->setPriority(10);
            $event->addTask($task);
        }
    }
}
