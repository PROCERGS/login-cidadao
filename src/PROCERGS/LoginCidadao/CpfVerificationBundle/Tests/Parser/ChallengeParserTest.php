<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\SelectMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeVoterRegistrationChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Parser\ChallengeParser;

class ChallengeParserTest extends TestCase
{
    public function testParseWithChoices()
    {
        $json = json_encode([
            'challenge' => $name = 'select_mother_initials',
            'cpf' => $cpf = '12345678901',
            'choices' => $choices = [],
            'attempts_left' => $attemptsLeft = 3,
        ]);

        /** @var SelectMotherInitialsChallenge $challenge */
        $challenge = ChallengeParser::parseJson($json);

        $this->assertInstanceOf(SelectMotherInitialsChallenge::class, $challenge);
        $this->assertSame($name, $challenge->getName());
        $this->assertSame($cpf, $challenge->getCpf());
        $this->assertSame($choices, $challenge->getChoices());
        $this->assertSame($attemptsLeft, $challenge->getAttemptsLeft());
    }

    public function testParseWithoutChoices()
    {
        $json = json_encode([
            'challenge' => $name = 'type_mother_initials',
            'cpf' => $cpf = '12345678901',
            'attempts_left' => $attemptsLeft = 3,
        ]);

        $challenge = ChallengeParser::parseJson($json);

        $this->assertInstanceOf(TypeMotherInitialsChallenge::class, $challenge);
        $this->assertSame($name, $challenge->getName());
        $this->assertSame($cpf, $challenge->getCpf());
        $this->assertSame($attemptsLeft, $challenge->getAttemptsLeft());
    }

    public function testParseInvalidJson()
    {
        $this->expectException(\RuntimeException::class);

        ChallengeParser::parseJson('{"invalid":true}');
    }

    public function testMissingAttemptsLeft()
    {
        $this->expectException(\RuntimeException::class);

        ChallengeParser::parseJson('{"challenge":"dummy"}');
    }

    public function testMissingCpf()
    {
        $json = json_encode([
            'challenge' => $name = TypeVoterRegistrationChallenge::CHALLENGE_NAME,
            'attempts_left' => $attemptsLeft = 3,
        ]);

        $challenge = ChallengeParser::parseJson($json, $cpf = '12345678901');

        $this->assertInstanceOf(TypeVoterRegistrationChallenge::class, $challenge);
        $this->assertSame($name, $challenge->getName());
        $this->assertSame($cpf, $challenge->getCpf());
        $this->assertSame($attemptsLeft, $challenge->getAttemptsLeft());
    }
}
