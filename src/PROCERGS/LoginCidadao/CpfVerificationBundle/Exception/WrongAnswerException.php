<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Exception;

use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
use Throwable;

class WrongAnswerException extends \RuntimeException
{
    /** @var ChallengeInterface */
    private $challenge;

    /**
     * @inheritDoc
     */
    public function __construct(
        ChallengeInterface $challenge,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->challenge = $challenge;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ChallengeInterface
     */
    public function getChallenge(): ChallengeInterface
    {
        return $this->challenge;
    }
}
