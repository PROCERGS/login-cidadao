<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Task;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\CPFValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class CompleteUserInfoTaskValidator
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * Allow the CompleteUserInfoTask to be skipped if the user has already authorized the RP and the RP didn't ask
     * for the Task explicitly.
     * @var bool
     */
    private $skipIfAuthorized;

    /**
     * CompleteUserInfoTaskValidator constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param bool $skipIfAuthorized
     */
    public function __construct(EventDispatcherInterface $dispatcher, $skipIfAuthorized)
    {
        $this->dispatcher = $dispatcher;
        $this->skipIfAuthorized = $skipIfAuthorized;
    }

    /**
     * Check if prompt=consent was requested (also checks the Nonce)
     *
     * @param Request $request
     * @return bool
     */
    public function shouldPromptConsent(Request $request)
    {
        return $request->get('prompt', null) === 'consent' && $this->isNonceValid($request);
    }

    /**
     * Checks if the Client/RP already has the user's authorization.
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @return bool
     */
    public function isClientAuthorized(PersonInterface $person, ClientInterface $client)
    {
        /** @var OAuthEvent $event */
        $event = $this->dispatcher->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($person, $client)
        );

        return $event->isAuthorizedClient();
    }

    /**
     * Check if the Request's route is valid for this Task
     * @param Request $request
     * @return bool
     */
    public function isRouteValid(Request $request)
    {
        $route = $request->get('_route');
        $scopes = $request->get('scope', false);

        return $route === '_authorize_validate' && false !== $scopes;
    }

    /**
     * Get the CompleteUserInfoTask for the $user's missing data
     *
     * @param PersonInterface $user
     * @param ClientInterface $client
     * @param Request $request
     * @return CompleteUserInfoTask|null
     */
    public function getCompleteUserInfoTask(PersonInterface $user, ClientInterface $client, Request $request)
    {
        if (!$this->isRouteValid($request)
            || $this->canSkipTask($request, $user, $client)
            || false === $scope = $request->get('scope', false)) {
            return null;
        }

        $scopes = explode(' ', $scope);
        $emptyClaims = array_intersect($scopes, $this->checkScopes($user));

        if (empty($emptyClaims) > 0) {
            return null;
        }

        return new CompleteUserInfoTask($client->getPublicId(), $emptyClaims, $request->get('nonce'));
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isNonceValid(Request $request)
    {
        $nonce = $request->get('nonce', null);

        // TODO: check is nonce was already used

        return $nonce !== null;
    }

    private function checkScopes(PersonInterface $user)
    {
        $fullName = $user->getFullName();
        $missingScope = $this->setSynonyms([
            'full_name' => $this->isFilled($fullName) && $this->isFilled($user->getSurname()),
            'phone_number' => $this->isFilled($user->getMobile()),
            'country' => $user->getCountry() instanceof Country,
            'state' => $user->getState() instanceof State,
            'city' => $user->getCity() instanceof City,
            'birthday' => $user->getBirthdate() instanceof \DateTime,
            'email_verified' => $user->getEmailConfirmedAt() instanceof \DateTime,
            'cpf' => $this->isFilled($user->getCpf()) && CPFValidator::isCPFValid($user->getCpf()),
        ], [
            'name' => 'full_name',
            'birthdate' => 'birthday',
            'surname' => 'full_name',
            'mobile' => 'phone_number',
            'email' => 'email_verified',
        ]);

        return array_keys(
            array_filter($missingScope, function ($value) {
                return !$value;
            })
        );
    }

    private function isFilled($value)
    {
        return $value && strlen($value) > 0;
    }

    private function setSynonyms(array $data, array $synonyms)
    {
        foreach ($synonyms as $synonym => $original) {
            $data[$synonym] = $data[$original];
        }

        return $data;
    }

    /**
     * Check if this Task can be skipped based on an existing Authorization
     *
     * @param Request $request
     * @param PersonInterface $user
     * @param ClientInterface $client
     * @return bool
     */
    private function canSkipTask(Request $request, PersonInterface $user, ClientInterface $client)
    {
        // Can this Task be skipped if the RP is already Authorized?
        if ($this->skipIfAuthorized) {
            // To force this task's execution, the RP MUST send prompt=consent and a nonce value.
            $shouldPromptConsent = $this->shouldPromptConsent($request);
            $isAuthorized = $this->isClientAuthorized($user, $client);

            // Skip if the RP is authorized and the Task wasn't explicitly requested
            if ($isAuthorized && !$shouldPromptConsent) {
                return true;
            }
        }

        return false;
    }
}
