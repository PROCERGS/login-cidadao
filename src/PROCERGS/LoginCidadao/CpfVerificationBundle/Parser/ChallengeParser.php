<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Parser;

use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeFactory;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;

class ChallengeParser
{
    public static function parseJson(string $json, string $cpf = null): ChallengeInterface
    {
        $decoded = json_decode($json, true);

        return self::parseArray($decoded, $cpf);
    }

    public static function parseArray(array $serialized, string $cpf = null): ChallengeInterface
    {
        if (!array_key_exists('challenge', $serialized)) {
            throw new \RuntimeException();
        }
        if (!array_key_exists('attempts_left', $serialized)) {
            throw new \RuntimeException();
        }

        $challengeName = $serialized['challenge'];
        $attemptsLeft = $serialized['attempts_left'];
        $cpf = $cpf ?? $serialized['cpf'] ?? null;
        $choices = $serialized['choices'] ?? [];

        return ChallengeFactory::create($challengeName, $attemptsLeft, $cpf, $choices);
    }
}
