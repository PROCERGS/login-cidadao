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

use GuzzleHttp\Client;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfNotSubscribedToNfgException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\SelectMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeBirthdayChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypePostalCodeChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeVoterRegistrationChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Parser\ChallengeParser;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class CpfVerificationService
{
    /** @var Client */
    private $client;

    /**
     * CpfVerificationService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $cpf
     * @return ChallengeInterface[]
     * @throws CpfNotSubscribedToNfgException
     */
    public function listAvailableChallenges(string $cpf): array
    {
        $challenges = $this->getAvailableChallengesFromApi($cpf);

        return $challenges;

        return [
            new TypeMotherInitialsChallenge(1, $cpf),
            new SelectMotherInitialsChallenge(2, $cpf, ['ABC', 'DEF', 'XYZ']),
            new TypePostalCodeChallenge(3, $cpf),
            new TypeBirthdayChallenge(4, $cpf),
            new TypeVoterRegistrationChallenge(5, $cpf),
        ];
    }

    /**
     * @param ChallengeInterface $challenge
     * @return ChallengeInterface
     * @throws CpfNotSubscribedToNfgException
     */
    public function selectChallenge(ChallengeInterface $challenge): ChallengeInterface
    {
        return $this->selectChallengeFromApi($challenge);

        return $challenge;
    }

    /**
     * @param ChallengeInterface $challenge
     * @param $answer
     * @return bool
     * @throws CpfNotSubscribedToNfgException
     */
    public function answerChallenge(ChallengeInterface $challenge, $answer): bool
    {
        // TODO: implement actual method

        return true;
    }

    private function getAvailableChallengesFromApi(string $cpf): array
    {
        $response = $this->client->get("cpf/{$cpf}/challenges");
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        if ($statusCode === 200) {
            return $this->parseChallengesList($body);
        }

        if ($statusCode === 403) {
            $response = json_decode($body);
            if ($response['error'] === CpfNotSubscribedToNfgException::ERROR_CODE) {
                throw new CpfNotSubscribedToNfgException($cpf, $response['message'] ?? null);
            }
        }

        if ($statusCode === 429) {
            throw new TooManyRequestsHttpException();
        }

        throw new \LogicException("Invalid response code {$statusCode} with body {$body}");
    }

    private function selectChallengeFromApi(ChallengeInterface $challenge): ChallengeInterface
    {
        $response = $this->client->get("cpf/{$challenge->getCpf()}/challenges/{$challenge->getName()}");
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();

        if ($statusCode === 200) {
            return ChallengeParser::parseJson((string)$response->getBody());
        }

        if ($statusCode === 403) {
            $response = json_decode($body);
            if ($response['error'] === CpfNotSubscribedToNfgException::ERROR_CODE) {
                throw new CpfNotSubscribedToNfgException($challenge->getCpf(), $response['message'] ?? null);
            }
        }

        if ($statusCode === 429) {
            throw new TooManyRequestsHttpException();
        }

        throw new \LogicException("Invalid response code {$statusCode} with body {$body}");
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
