<?php

namespace LoginCidadao\CoreBundle\EventListener;

use LoginCidadao\CoreBundle\Event\GetTasksEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Model\MigratePasswordEncoderTask;
use LoginCidadao\CoreBundle\Model\Task;
use LoginCidadao\CoreBundle\Service\IntentManager;
use LoginCidadao\CoreBundle\Service\TasksManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\CoreBundle\Exception\RedirectResponseException;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Doctrine\ORM\EntityManager;

class LoggedInUserListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var RouterInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManager */
    private $em;

    /** @var IntentManager */
    private $intentManager;

    /** @var TasksManager */
    private $tasksManager;

    /** @var string */
    private $defaultPasswordEncoder;

    /** @var boolean */
    private $requireEmailValidation;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        RouterInterface $router,
        Session $session,
        TranslatorInterface $translator,
        IntentManager $intentManager,
        TasksManager $tasksManager,
        $defaultPasswordEncoder,
        $requireEmailValidation
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
        $this->intentManager = $intentManager;
        $this->tasksManager = $tasksManager;

        $this->defaultPasswordEncoder = $defaultPasswordEncoder;
        $this->requireEmailValidation = $requireEmailValidation;
    }

    public function onKernelRequest(GetResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $token = $this->tokenStorage->getToken();

        if (is_null($token) || $token instanceof OAuthToken ||
            $this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') === false
        ) {
            return;
        }
        if (!($token->getUser() instanceof PersonInterface)) {
            // We don't have a PersonInterface... Nothing to do here.
            return;
        }

        try {
            $this->handleTargetPath($event);
            $tasks = $this->checkTasks($event, $dispatcher);
            if (!$tasks) {
                $this->checkIntent($event);
            }
            $this->checkUnconfirmedEmail();
        } catch (RedirectResponseException $e) {
            $event->setResponse($e->getResponse());
        }
    }

    private function handleTargetPath(GetResponseEvent $event)
    {
        $route = $event->getRequest()->get('_route');
        if ($route !== 'lc_home' && $route !== 'fos_user_security_login') {
            return;
        }
        $key = '_security.main.target_path'; #where "main" is your firewall name
        //check if the referer session key has been set
        if ($this->session->has($key)) {
            //set the url based on the link they were trying to access before being authenticated
            $url = $this->session->get($key);

            //remove the session key
            $this->session->remove($key);
        } else {
            $url = $this->router->generate('lc_dashboard');
        }

        return $this->redirectUrl($url);
    }

    protected function checkUnconfirmedEmail()
    {
        if ($this->requireEmailValidation) {
            // Thre is a Task for that already
            return;
        }
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if (is_null($user->getEmailConfirmedAt())) {
            $params = array('%url%' => $this->router->generate('lc_resend_confirmation_email'));
            $title = $this->translator->trans('notification.unconfirmed.email.title');
            $text = $this->translator->trans(
                'notification.unconfirmed.email.shortText',
                $params
            );
            $alert = sprintf("<strong>%s</strong> %s", $title, $text);

            $this->session->getFlashBag()->add('alert.unconfirmed.email', $alert);
        }
    }

    private function redirectRoute($name, $parameters = array())
    {
        $url = $this->router->generate($name, $parameters);

        return $this->redirectUrl($url);
    }

    private function redirectUrl($url)
    {
        throw new RedirectResponseException(new RedirectResponse($url));
    }

    private function checkTasks(GetResponseEvent $event, EventDispatcherInterface $dispatcher)
    {
        $tasksEvent = new GetTasksEvent($event->getRequest());
        $dispatcher->dispatch(LoginCidadaoCoreEvents::GET_TASKS, $tasksEvent);
        $routeName = $event->getRequest()->get('_route');

        $tasks = $tasksEvent->getTasks();
        $task = $this->tasksManager->getNextTask($tasks, $routeName);

        if (!($task instanceof Task)) {
            return false;
        }
        if ($this->tasksManager->checkTaskSkipped($task)) {
            return false;
        }

        $target = $task->getTarget();

        if ($task instanceof MigratePasswordEncoderTask) {
            $this->session->set('force_password_change', true);
        }
        // If the user is not trying to access one of the task's routes, redirect to the default route
        if (false === $task->isTaskRoute($routeName)) {
            $this->intentManager->setIntent($event->getRequest(), false);
            $this->redirectRoute($target[0], $target[1]);
        }

        return true;
    }

    private function checkIntent(GetResponseEvent $event)
    {
        $intent = $this->intentManager->consumeIntent($event->getRequest());

        if ($intent) {
            $this->redirectUrl($intent);
        }
    }
}
