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

use LoginCidadao\CoreBundle\Entity\InvalidateSessionRequestRepository;
use LoginCidadao\CoreBundle\Model\ConfirmEmailTask;
use LoginCidadao\CoreBundle\Model\InvalidateSessionTask;
use LoginCidadao\CoreBundle\Model\MigratePasswordEncoderTask;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var InvalidateSessionRequestRepository */
    protected $invalidateSessionRequestRepository;

    /** @var array */
    protected $options;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param InvalidateSessionRequestRepository $invalidateSessionRequestRepository
     * @param bool $mandatoryEmailValidation
     * @param $defaultPasswordEncoder
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        InvalidateSessionRequestRepository $invalidateSessionRequestRepository,
        $mandatoryEmailValidation,
        $defaultPasswordEncoder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->invalidateSessionRequestRepository = $invalidateSessionRequestRepository;
        $this->options['mandatoryEmailValidation'] = $mandatoryEmailValidation;
        $this->options['defaultPasswordEncoder'] = $defaultPasswordEncoder;
    }


    public static function getSubscribedEvents()
    {
        return [
            TaskStackEvents::GET_TASKS => ['onGetTasks', 100],
        ];
    }

    public function onGetTasks(GetTasksEvent $event)
    {
        /** @var PersonInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof PersonInterface) {
            return;
        }

        $this->checkEmailTask($event, $user);
        $this->checkPasswordMigrationTask($event, $user);
        $this->checkSessionInvalidation($event, $user);
    }

    private function checkEmailTask(GetTasksEvent $event, PersonInterface $user)
    {
        $target = new RouteTaskTarget('task_confirm_email');
        $task = new ConfirmEmailTask($target, true);

        if ($this->options['mandatoryEmailValidation'] && !($user->getEmailConfirmedAt() instanceof \DateTime)) {
            $event->forceAddUniqueTask($task);
        }
    }

    private function checkPasswordMigrationTask(GetTasksEvent $event, PersonInterface $user)
    {
        $target = new RouteTaskTarget('fos_user_change_password');
        $task = new MigratePasswordEncoderTask($target);

        if ($user->getEncoderName() !== $this->options['defaultPasswordEncoder']) {
            $event->addTask($task);
        } else {
            $event->setTaskSkipped($task);
        }
    }

    private function checkSessionInvalidation(GetTasksEvent $event, PersonInterface $person)
    {
        $target = new RouteTaskTarget('fos_user_security_logout');
        $task = new InvalidateSessionTask($target);

        if (!$this->authChecker->isGranted('FEATURE_INVALIDATE_SESSIONS')) {
            return;
        }

        $repo = $this->invalidateSessionRequestRepository;
        $request = $repo->findMostRecent($person);

        $sessionCreation = $event->getRequest()->getSession()->getMetadataBag()->getCreated();
        if ($request === null || $sessionCreation > $request->getRequestedAt()->getTimestamp()) {
            return;
        }

        $event->setTaskSkipped($task, false);
        $event->addTask($task);
    }
}
