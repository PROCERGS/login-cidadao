<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\ChallengeFactory;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\SelectMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeBirthdayChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypePostalCodeChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeVoterRegistrationChallenge;

class ChallengeFactoryTest extends TestCase
{
    public function testSelectMotherInitialsChallenge()
    {
        $class = SelectMotherInitialsChallenge::class;
        $this->assertInstanceOf($class, ChallengeFactory::create($class::CHALLENGE_NAME, 1, 'cpf'));
    }

    public function testTypeMotherInitialsChallenge()
    {
        $class = TypeMotherInitialsChallenge::class;
        $this->assertInstanceOf($class, ChallengeFactory::create($class::CHALLENGE_NAME, 1, 'cpf'));
    }

    public function testTypeBirthdayChallenge()
    {
        $class = TypeBirthdayChallenge::class;
        $this->assertInstanceOf($class, ChallengeFactory::create($class::CHALLENGE_NAME, 1, 'cpf'));
    }

    public function testTypePostalCodeChallenge()
    {
        $class = TypePostalCodeChallenge::class;
        $this->assertInstanceOf($class, ChallengeFactory::create($class::CHALLENGE_NAME, 1, 'cpf'));
    }

    public function testTypeVoterRegistrationChallenge()
    {
        $class = TypeVoterRegistrationChallenge::class;
        $this->assertInstanceOf($class, ChallengeFactory::create($class::CHALLENGE_NAME, 1, 'cpf'));
    }

    public function testUnsupportedChallenge()
    {
        $this->expectException(\InvalidArgumentException::class);

        ChallengeFactory::create('unsupported_challenge_here', 1, 'cpf');
    }
}
