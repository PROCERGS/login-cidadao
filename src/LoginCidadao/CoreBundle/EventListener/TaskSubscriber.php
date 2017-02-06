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
use LoginCidadao\CoreBundle\Event\GetTasksEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Model\ConfirmEmailTask;
use LoginCidadao\CoreBundle\Model\InvalidateSessionTask;
use LoginCidadao\CoreBundle\Model\MigratePasswordEncoderTask;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class TaskSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var HttpUtils */
    protected $httpUtils;

    /** @var InvalidateSessionRequestRepository */
    protected $invalidateSessionRequestRepository;

    /** @var array */
    protected $options;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param HttpUtils $httpUtils
     * @param InvalidateSessionRequestRepository $invalidateSessionRequestRepository
     * @param bool $mandatoryEmailValidation
     * @param $defaultPasswordEncoder
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        HttpUtils $httpUtils,
        InvalidateSessionRequestRepository $invalidateSessionRequestRepository,
        $mandatoryEmailValidation,
        $defaultPasswordEncoder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->httpUtils = $httpUtils;
        $this->invalidateSessionRequestRepository = $invalidateSessionRequestRepository;
        $this->options['mandatoryEmailValidation'] = $mandatoryEmailValidation;
        $this->options['defaultPasswordEncoder'] = $defaultPasswordEncoder;
    }


    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoCoreEvents::GET_TASKS => ['onGetTasks', 100],
        ];
    }

    /**
     * @param GetTasksEvent $event
     */
    public function onGetTasks(GetTasksEvent $event)
    {
        /** @var PersonInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!($user instanceof PersonInterface)) {
            return;
        }

        if ($this->options['mandatoryEmailValidation'] && !($user->getEmailConfirmedAt() instanceof \DateTime)) {
            $task = (new ConfirmEmailTask())
                ->setIsMandatory(true);
            $event->addTask($task);
        }

        if ($user->getEncoderName() !== $this->options['defaultPasswordEncoder']) {
            $event->addTask(new MigratePasswordEncoderTask());
        }

        $this->checkSessionInvalidation($event);
    }

    private function checkSessionInvalidation(GetTasksEvent $event)
    {
        if (!$this->authChecker->isGranted('FEATURE_INVALIDATE_SESSIONS')) {
            return;
        }

        $person = $this->tokenStorage->getToken()->getUser();
        $repo = $this->invalidateSessionRequestRepository;
        $request = $repo->findMostRecent($person);

        $sessionCreation = $event->getRequest()->getSession()->getMetadataBag()->getCreated();
        if ($request === null ||
            $sessionCreation > $request->getRequestedAt()->getTimestamp()
        ) {
            return;
        }

        $event->addTask(new InvalidateSessionTask());
    }
}
