<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Model;

abstract class AbstractChallenge implements ChallengeInterface
{
    /** @var string */
    private $cpf;

    /** @var int */
    private $attemptsLeft;

    /**
     * Challenge constructor.
     * @param string $cpf
     * @param int $attemptsLeft
     */
    public function __construct(int $attemptsLeft, string $cpf)
    {
        $this->cpf = $cpf;
        $this->attemptsLeft = $attemptsLeft;
    }

    /**
     * @return string
     */
    public function getCpf(): string
    {
        return $this->cpf;
    }

    /**
     * @return int
     */
    public function getAttemptsLeft(): int
    {
        return $this->attemptsLeft;
    }
}
