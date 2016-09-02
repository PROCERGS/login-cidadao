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

use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Event\GetTasksEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Model\CompleteUserInfoTask;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\CPFValidator;
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

    /**
     * TaskSubscriber constructor.
     * @param TokenStorage $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param HttpUtils $httpUtils
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        HttpUtils $httpUtils
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->httpUtils = $httpUtils;
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
    public function onGetTasks(GetTasksEvent $event)
    {
        try {
            /** @var PersonInterface $user */
            $user = $this->tokenStorage->getToken()->getUser();

            if (!($user instanceof PersonInterface)) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $task = new CompleteUserInfoTask();
        $request = $event->getRequest();
        $route = $request->get('_route');
        $scopes = $request->get('scope', false);
        if (
            $route !== '_authorize_validate'
            && !$task->isSkipRoute($route)
            && (false === $task->isTaskRoute($route) || !$scopes)
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
            $task
                ->setClientId($request->get('client_id'))
                ->setScope($emptyClaims);
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
            case 'full_name':
            case 'surname':
                $value = $user->getFullName();
                break;
            case 'mobile':
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
                return $user->getEmailConfirmedAt() instanceof \DateTime;
            case 'cpf':
                $cpf = $user->getCpf();

                return $cpf && CPFValidator::isCPFValid($cpf);
            default:
                return true;
        }

        return $value && strlen($value) > 0;
    }
}
