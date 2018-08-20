<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Service;

use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfVerificationException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Parser\ChallengeParser;

class CpfVerificationService
{
    /** @var CpfVerificationHttpService */
    private $httpService;

    /**
     * CpfVerificationService constructor.
     * @param CpfVerificationHttpService $httpService
     */
    public function __construct(CpfVerificationHttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    /**
     * @param string $cpf
     * @return ChallengeInterface[]
     * @throws CpfVerificationException
     */
    public function listAvailableChallenges(string $cpf): array
    {
        $body = $this->httpService->sendGetRequest($this->httpService->getListChallengesPath($cpf));

        return $this->parseChallengesList($body);
    }

    /**
     * @param ChallengeInterface $challenge
     * @return ChallengeInterface
     * @throws CpfVerificationException
     */
    public function selectChallenge(ChallengeInterface $challenge): ChallengeInterface
    {
        $body = $this->httpService->sendGetRequest($this->httpService->getChallengePath($challenge));

        return ChallengeParser::parseJson($body);
    }

    /**
     * @param ChallengeInterface $challenge
     * @param $answer
     * @return bool
     * @throws CpfVerificationException
     */
    public function answerChallenge(ChallengeInterface $challenge, $answer): bool
    {
        return $this->httpService->submitAnswer($challenge, $answer);
    }

    /**
     * @param string $json
     * @return ChallengeInterface[]
     */
    private function parseChallengesList(string $json): array
    {
        $response = json_decode($json, true);
        $challenges = [];
        if (array_key_exists('challenges', $response)) {
            $cpf = $response['cpf'];
            foreach ($response['challenges'] as $challenge) {
                $challenges[] = ChallengeParser::parseArray($challenge, $cpf);
            }
        }

        return $challenges;
    }
}
