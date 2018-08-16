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

class ChallengeFactory
{
    public static function create(
        string $challengeName,
        int $attemptsLeft,
        string $cpf,
        array $choices = null
    ): ChallengeInterface {
        switch ($challengeName) {
            case SelectMotherInitialsChallenge::CHALLENGE_NAME:
                return new SelectMotherInitialsChallenge($attemptsLeft, $cpf, $choices ?? []);
            case TypeBirthdayChallenge::CHALLENGE_NAME:
                return new TypeBirthdayChallenge($attemptsLeft, $cpf);
            case TypeMotherInitialsChallenge::CHALLENGE_NAME:
                return new TypeMotherInitialsChallenge($attemptsLeft, $cpf);
            case TypePostalCodeChallenge::CHALLENGE_NAME:
                return new TypePostalCodeChallenge($attemptsLeft, $cpf);
            case TypeVoterRegistrationChallenge::CHALLENGE_NAME:
                return new TypeVoterRegistrationChallenge($attemptsLeft, $cpf);
            default:
                throw new \InvalidArgumentException("Unsupported challenge '{$challengeName}'");
        }
    }
}
