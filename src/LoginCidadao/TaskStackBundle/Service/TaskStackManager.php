<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Service;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\Exception\UnsupportedTargetException;
use LoginCidadao\TaskStackBundle\Model\IntentTask;
use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;
use LoginCidadao\TaskStackBundle\Model\TaskInterface;
use LoginCidadao\TaskStackBundle\Model\TaskStack;
use LoginCidadao\TaskStackBundle\Model\TaskTargetInterface;
use LoginCidadao\TaskStackBundle\Model\UrlTaskTarget;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskStackManager implements TaskStackManagerInterface
{
    const SKIPPED_TASKS_KEY = 'tasks.skipped';
    const TASKS_STACK_KEY = 'tasks.stack';

    /** @var SessionInterface */
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * TaskStackManager constructor.
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $dispatcher
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return TaskStack
     */
    private function getStack()
    {
        /** @var TaskStack $stack */
        $stack = $this->session->get(self::TASKS_STACK_KEY, new TaskStack());

        return $stack;
    }

    /**
     * @return array
     */
    private function getSkippedTasks()
    {
        return $this->session->get(self::SKIPPED_TASKS_KEY, []);
    }

    /**
     * Stacks the given $task, making sure there are no duplicates and that it was not skipped.
     *
     * @param TaskInterface $task
     * @param IntentTask|null $intentTask
     * @return TaskStackManagerInterface
     */
    public function addNotSkippedTaskOnce(TaskInterface $task, IntentTask $intentTask = null)
    {
        $stack = $this->getStack();
        $isSkipped = !$task->isMandatory() && $this->isSkipped($task);
        if ($isSkipped === false && $stack->hasTask($task) === false) {
            if ($intentTask) {
                $this->addNotSkippedTaskOnce($intentTask);
                $stack = $this->getStack();
            }
            $stack->push($task);
            $this->updateStack($stack);
        }

        return $this;
    }

    /**
     * @param TaskInterface $task
     * @param bool $isSkipped
     * @return TaskStackManagerInterface|void
     */
    public function setTaskSkipped(TaskInterface $task, $isSkipped = true)
    {
        $skipped = $this->getSkippedTasks();
        if ($isSkipped) {
            $skipped[$task->getId()] = true;
        } else {
            unset($skipped[$task->getId()]);
        }
        $this->updateSkipped($skipped);
    }

    /**
     * @param Request $request
     * @param Response|null $defaultResponse
     * @return Response
     */
    public function processRequest(Request $request, Response $defaultResponse = null)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || $token instanceof OAuthToken || !$token->getUser() instanceof PersonInterface) {
            return $defaultResponse;
        }

        $this->populateStack($request);

        $stack = $this->getStack();

        if ($stack->isEmpty()) {
            return $defaultResponse;
        }

        /** @var TaskInterface $task */
        $task = $stack->top();

        // If it's an IntentTask, it's just a redirect, so we have to consume it ourselves.
        if ($task instanceof IntentTask) {
            $stack->pop();
            $this->updateStack($stack);
        }

        if ($this->isSkipped($task)) {
            $stack->pop();
            $this->updateStack($stack);

            return $defaultResponse;
        }

        if (false === $task->isTaskRoute($request->attributes->get('_route'))) {
            $target = $task->getTarget();
            $url = $this->getTargetUrl($target);

            return new RedirectResponse($url);
        }

        return $defaultResponse;
    }

    public function emptyStack()
    {
        $this->updateStack(new TaskStack());
    }

    /**
     * @param Request $request
     */
    private function populateStack(Request $request)
    {
        $event = new GetTasksEvent($this, $request);
        $this->dispatcher->dispatch(TaskStackEvents::GET_TASKS, $event);
    }

    /**
     * @param TaskInterface $task
     * @return bool
     */
    private function isSkipped(TaskInterface $task)
    {
        return false !== array_key_exists($task->getId(), $this->getSkippedTasks());
    }

    private function updateStack(TaskStack $stack)
    {
        $this->session->set(self::TASKS_STACK_KEY, $stack);
    }

    private function updateSkipped(array $skippedTasks)
    {
        $this->session->set(self::SKIPPED_TASKS_KEY, $skippedTasks);
    }

    /**
     * @return TaskInterface|null
     */
    public function getCurrentTask()
    {
        $stack = $this->getStack();

        /** @var TaskInterface|null $task */
        $task = $stack->isEmpty() ? null : $stack->top();

        return $task;
    }

    public function getNextTask()
    {
        $stack = $this->getStack();
        $peeked = 0;
        foreach ($stack as $task) {
            if ($peeked < 1) {
                $peeked++;
                continue;
            }

            return $task;
        }

        return null;
    }

    /**
     * Counts how many Tasks are in the Stack
     *
     * @return int
     */
    public function countTasks()
    {
        return $this->getStack()->count();
    }

    /**
     * @return bool
     */
    public function hasIntentTask()
    {
        return $this->getStack()->hasIntentTask();
    }

    /**
     * Converts a TaskTargetInterface into a string URL.
     *
     * @param TaskTargetInterface $target
     * @return string
     */
    public function getTargetUrl(TaskTargetInterface $target)
    {
        if ($target instanceof RouteTaskTarget) {
            $url = $this->router->generate($target->getRoute(), $target->getParameters());
        } elseif ($target instanceof UrlTaskTarget) {
            $url = $target->getUrl();
        } else {
            throw new UnsupportedTargetException($target);
        }

        return $url;
    }
}
