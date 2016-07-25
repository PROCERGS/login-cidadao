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
use LoginCidadao\CoreBundle\Model\ConfirmEmailTask;
use LoginCidadao\CoreBundle\Model\MigratePasswordEncoderTask;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\HttpUtils;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var HttpUtils */
    protected $httpUtils;

    /** @var array */
    protected $options;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param HttpUtils $httpUtils
     * @param bool $mandatoryEmailValidation
     */
    public function __construct(
        TokenStorage $tokenStorage,
        HttpUtils $httpUtils,
        $mandatoryEmailValidation,
        $defaultPasswordEncoder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->httpUtils = $httpUtils;
        $this->options['mandatoryEmailValidation'] = $mandatoryEmailValidation;
        $this->options['defaultPasswordEncoder'] = $defaultPasswordEncoder;
    }


    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoCoreEvents::GET_TASKS => ['onGetTasks', 0],
        ];
    }

    /**
     * @param GetTasksEvent $event
     */
    public function onGetTasks(GetTasksEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var PersonInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!($user instanceof PersonInterface)) {
            return;
        }

        if ($this->options['mandatoryEmailValidation'] && $user->getConfirmationToken()) {
            $task = (new ConfirmEmailTask())
                ->setIsMandatory(true);
            $event->addTask($task);
        }

        if (true || $user->getEncoderName() !== $this->options['defaultPasswordEncoder']) {
            $event->addTask(new MigratePasswordEncoderTask());
        }
    }
}
