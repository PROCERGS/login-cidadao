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

use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use LoginCidadao\ValidationBundle\Validator\Constraints\CPFValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /** @var ClientManager */
    private $clientManager;

    /** @var bool */
    private $skipCompletionTaskIfAuthorized;

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param HttpUtils $httpUtils
     * @param ClientManager $clientManager
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        HttpUtils $httpUtils,
        ClientManager $clientManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->httpUtils = $httpUtils;
        $this->clientManager = $clientManager;
    }


    public static function getSubscribedEvents()
    {
        return [
            TaskStackEvents::GET_TASKS => ['onGetTasks', 50],
        ];
    }

    public function onGetTasks(GetTasksEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        try {
            /** @var PersonInterface $user */
            $user = $this->tokenStorage->getToken()->getUser();

            if (!$user instanceof PersonInterface) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $request = $event->getRequest();

        $route = $request->get('_route');
        $scopes = $request->get('scope', false);
        if ($route !== '_authorize_validate' || !$scopes) {
            return;
        }

        $clientId = $request->get('client_id', $request->attributes->get('clientId'));

        // To force this task's execution, the RP MUST send prompt=consent and a nonce value.
        $promptConsent = $request->get('prompt', null) == 'consent'
            && $request->get('nonce', null) !== null;

        if (!$clientId) {
            return;
        }
        if ($this->skipCompletionTaskIfAuthorized
            && $this->isAuthorizedClient($dispatcher, $clientId)
            && !$promptConsent
        ) {
            return;
        }

        $scopes = explode(' ', $scopes);
        $emptyClaims = [];
        foreach ($scopes as $scope) {
            if ($this->checkScope($user, $scope)) {
                continue;
            }
            $emptyClaims[] = $scope;
        }

        if (count($emptyClaims) > 0) {
            $task = new CompleteUserInfoTask($clientId, $emptyClaims, $request->get('nonce'));
            $event->addTask($task);
        }
    }

    /**
     * @param PersonInterface $user
     * @param string $scope
     * @return bool
     */
    private function checkScope(PersonInterface $user, $scope)
    {
        // 'id_cards', 'addresses'
        switch ($scope) {
            case 'name':
            case 'full_name':
            case 'surname':
                $value = $user->getFullName();

                return $value && strlen($value) > 0 && strlen($user->getSurname()) > 0;
                break;
            case 'mobile':
            case 'phone_number':
                $value = $user->getMobile();
                break;
            case 'country':
                return $user->getCountry() instanceof Country;
            case 'state':
                return $user->getState() instanceof State;
            case 'city':
                return $user->getCity() instanceof City;
            case 'birthdate':
                return $user->getBirthdate() instanceof \DateTime;
            case 'email':
            case 'email_verified':
                return $user->getEmailConfirmedAt() instanceof \DateTime;
            case 'cpf':
                $cpf = $user->getCpf();

                return $cpf && CPFValidator::isCPFValid($cpf);
            default:
                return true;
        }

        return $value && strlen($value) > 0;
    }

    private function isAuthorizedClient(EventDispatcherInterface $dispatcher, $clientId)
    {
        $client = $this->clientManager->getClientById($clientId);

        /** @var OAuthEvent $event */
        $event = $dispatcher->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($this->tokenStorage->getToken()->getUser(), $client)
        );

        return $event->isAuthorizedClient();
    }

    public function setSkipCompletionTaskIfAuthorized($skip)
    {
        $this->skipCompletionTaskIfAuthorized = $skip;
    }
}
