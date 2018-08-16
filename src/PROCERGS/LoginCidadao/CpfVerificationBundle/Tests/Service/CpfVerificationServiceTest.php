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
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Service\CpfVerificationService;

class CpfVerificationServiceTest extends TestCase
{
    public function testListAvailableChallenges()
    {
        $cpf = '12345678901';
        $response = json_encode([
            'cpf' => $cpf,
            'challenges' => [
                ['challenge' => 'type_mother_initials', 'attempts_left' => 3],
                ['challenge' => 'select_mother_initials', 'attempts_left' => 2],
            ],
        ]);
        $client = $this->getHttpClient([
            new Response(200, [], $response),
        ]);
        $service = new CpfVerificationService($client);
        $challenges = $service->listAvailableChallenges($cpf);

        foreach ($challenges as $challenge) {
            $this->assertInstanceOf(ChallengeInterface::class, $challenge);
            $this->assertSame($cpf, $challenge->getCpf());
        }
    }

    public function testSelectChallenge()
    {
        $cpf = '12345678901';
        $response = json_encode(['challenge' => 'type_mother_initials', 'attempts_left' => 3, 'cpf' => $cpf]);
        $client = $this->getHttpClient([
            new Response(200, [], $response),
        ]);

        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $service = new CpfVerificationService($client);
        $this->assertInstanceOf(TypeMotherInitialsChallenge::class, $service->selectChallenge($challenge));
    }

    public function testAnswerChallenge()
    {
        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $service = new CpfVerificationService($this->getHttpClient([new Response(204)]));
        $this->assertTrue($service->answerChallenge($challenge, 'answer'));
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
