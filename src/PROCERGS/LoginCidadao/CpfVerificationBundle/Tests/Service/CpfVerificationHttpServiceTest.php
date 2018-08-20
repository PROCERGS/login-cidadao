<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfNotSubscribedToNfgException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfVerificationException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\WrongAnswerException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Service\CpfVerificationHttpService;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class CpfVerificationHttpServiceTest extends TestCase
{
    /**
     * @throws CpfVerificationException
     */
    public function testSubmitCorrectAnswerNoContent()
    {
        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $client = $this->getHttpClient([new Response(204)]);

        $service = new CpfVerificationHttpService($client);
        $this->assertTrue($service->submitAnswer($challenge, 'answer'));
    }

    /**
     * @throws CpfVerificationException
     */
    public function testSubmitCorrectAnswerWithContent()
    {
        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $client = $this->getHttpClient([new Response(200, [], 'ok')]);

        $service = new CpfVerificationHttpService($client);
        $this->assertTrue($service->submitAnswer($challenge, 'answer'));
    }

    /**
     * @throws CpfVerificationException
     */
    public function testSubmitAnswerOverQuota()
    {
        $this->expectException(TooManyRequestsHttpException::class);

        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $client = $this->getHttpClient([new Response(429)]);

        $service = new CpfVerificationHttpService($client);
        $service->submitAnswer($challenge, 'answer');
    }

    /**
     * @throws CpfVerificationException
     */
    public function testSubmitWrongAnswer()
    {
        $this->expectException(WrongAnswerException::class);

        $payload = json_encode(['error' => WrongAnswerException::ERROR_CODE]);

        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $client = $this->getHttpClient([new Response(403, [], $payload)]);

        $service = new CpfVerificationHttpService($client);
        $service->submitAnswer($challenge, 'answer');
    }

    /**
     * @throws CpfVerificationException
     */
    public function testSubmitAnswerWithInvalidResponse()
    {
        $this->expectException(\LogicException::class);

        $payload = json_encode(['message' => 'invalid']);

        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $client = $this->getHttpClient([new Response(403, [], $payload)]);

        $service = new CpfVerificationHttpService($client);
        $service->submitAnswer($challenge, 'answer');
    }

    /**
     * @throws CpfVerificationException
     */
    public function testSuccessfulGetRequest()
    {
        $payload = json_encode(['message' => 'success']);

        $client = $this->getHttpClient([new Response(200, [], $payload)]);

        $service = new CpfVerificationHttpService($client);
        $this->assertSame($payload, $service->sendGetRequest('some/uri'));
    }

    /**
     * @throws CpfVerificationException
     */
    public function testGetRequestWithUnregisteredNfgPerson()
    {
        $this->expectException(CpfNotSubscribedToNfgException::class);

        $payload = json_encode(['error' => CpfNotSubscribedToNfgException::ERROR_CODE, 'cpf' => '12345678901']);

        $client = $this->getHttpClient([new Response(403, [], $payload)]);

        $service = new CpfVerificationHttpService($client);
        $service->sendGetRequest('some/uri');
    }

    /**
     * @throws CpfVerificationException
     */
    public function testGetRequestOverQuota()
    {
        $this->expectException(TooManyRequestsHttpException::class);

        $client = $this->getHttpClient([new Response(429)]);

        $service = new CpfVerificationHttpService($client);
        $service->sendGetRequest('some/uri');
    }

    /**
     * @throws CpfVerificationException
     */
    public function testGetRequestWithInvalidResponse()
    {
        $this->expectException(\LogicException::class);

        $client = $this->getHttpClient([new Response(403, [], '')]);

        $service = new CpfVerificationHttpService($client);
        $service->sendGetRequest('some/uri');
    }

    public function testGetListChallengesPath()
    {
        $service = new CpfVerificationHttpService($this->getHttpClient());
        $this->assertSame('cpf/12345678901/challenges', $service->getListChallengesPath('12345678901'));
    }

    public function testGetChallengePath()
    {
        $challenge = $this->createMock(ChallengeInterface::class);
        $challenge->expects($this->once())->method('getCpf')->willReturn('12345678901');
        $challenge->expects($this->once())->method('getName')->willReturn('my_challenge');

        $service = new CpfVerificationHttpService($this->getHttpClient());
        $this->assertSame('cpf/12345678901/challenges/my_challenge', $service->getChallengePath($challenge));
    }

    /**
     * @param array $responses
     * @return Client
     */
    private function getHttpClient(array $responses = []): Client
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
