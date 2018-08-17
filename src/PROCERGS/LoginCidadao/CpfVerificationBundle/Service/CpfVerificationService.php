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
use GuzzleHttp\Exception\RequestException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfNotSubscribedToNfgException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfVerificationException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\WrongAnswerException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
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
     * @throws CpfVerificationException
     */
    public function listAvailableChallenges(string $cpf): array
    {
        $body = $this->sendGetRequest($this->getListChallengesPath($cpf));

        return $this->parseChallengesList($body);
    }

    /**
     * @param ChallengeInterface $challenge
     * @return ChallengeInterface
     * @throws CpfVerificationException
     */
    public function selectChallenge(ChallengeInterface $challenge): ChallengeInterface
    {
        $body = $this->sendGetRequest($this->getChallengePath($challenge));

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
        return $this->submitAnswer($challenge, $answer);
    }

    /**
     * @param ChallengeInterface $challenge
     * @param string $answer
     * @return bool
     * @throws CpfVerificationException
     */
    private function submitAnswer(ChallengeInterface $challenge, string $answer)
    {
        try {
            $response = $this->client->post($this->getChallengePath($challenge), [
                'form_params' => ['answer' => $answer],
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        $response = json_decode($body, true);

        if ($statusCode === 200 || $statusCode === 204) {
            return true;
        }

        if ($statusCode === 429) {
            throw $this->getTooManyRequestsException($response['message'] ?? null);
        }

        if ($statusCode === 403) {
            if ($response['error'] === WrongAnswerException::ERROR_CODE) {
                throw new WrongAnswerException($challenge, $response['message'] ?? "Wrong answer: {$answer}");
            }
        }

        throw $this->getInvalidResponseException($statusCode, $body);
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

    private function getListChallengesPath(string $cpf): string
    {
        return "cpf/{$cpf}/challenges";
    }

    private function getChallengePath(ChallengeInterface $challenge): string
    {
        return "cpf/{$challenge->getCpf()}/challenges/{$challenge->getName()}";
    }

    /**
     * @param string $uri
     * @return string
     * @throws CpfVerificationException
     */
    private function sendGetRequest(string $uri): string
    {
        try {
            $response = $this->client->get($uri);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        if ($statusCode === 200) {
            return $body;
        }

        $response = json_decode($body);
        if ($statusCode === 403) {
            if ($response['error'] === CpfNotSubscribedToNfgException::ERROR_CODE) {
                throw new CpfNotSubscribedToNfgException($response['cpf'], $response['message'] ?? null);
            }
        }

        if ($statusCode === 429) {
            throw $this->getTooManyRequestsException($response['message'] ?? null);
        }

        throw $this->getInvalidResponseException($statusCode, $body);
    }

    private function getInvalidResponseException($statusCode, $body): \LogicException
    {
        return new \LogicException("Invalid response code \"{$statusCode}\" with body \"{$body}\"");
    }

    private function getTooManyRequestsException($message = null): TooManyRequestsHttpException
    {
        return new TooManyRequestsHttpException(null, $message);
    }
}
