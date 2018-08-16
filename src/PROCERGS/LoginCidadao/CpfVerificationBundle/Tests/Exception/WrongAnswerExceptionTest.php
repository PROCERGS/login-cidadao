<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\Exception;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\WrongAnswerException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeInterface;

class WrongAnswerExceptionTest extends TestCase
{
    public function testException()
    {
        /** @var ChallengeInterface|MockObject $challenge */
        $challenge = $this->createMock(ChallengeInterface::class);

        $e = new WrongAnswerException($challenge);
        $this->assertSame($challenge, $e->getChallenge());
    }
}
