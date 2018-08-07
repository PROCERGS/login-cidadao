<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Event;

use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class AccountRecoveryDataEditEvent extends Event
{
    /** @var AccountRecoveryData */
    private $accountRecoveryData;

    /** @var Response */
    private $response;

    /**
     * AccountRecoveryDataEditEvent constructor.
     * @param AccountRecoveryData $accountRecoveryData
     * @param Response|null $response
     */
    public function __construct(AccountRecoveryData $accountRecoveryData, Response $response = null)
    {
        $this->accountRecoveryData = $accountRecoveryData;
        $this->response = $response;
    }

    /**
     * @return AccountRecoveryData
     */
    public function getAccountRecoveryData(): AccountRecoveryData
    {
        return $this->accountRecoveryData;
    }

    /**
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     * @return AccountRecoveryDataEditEvent
     */
    public function setResponse(Response $response): AccountRecoveryDataEditEvent
    {
        $this->response = $response;

        return $this;
    }
}
