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
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\SelectMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeBirthdayChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypePostalCodeChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\TypeVoterRegistrationChallenge;

class ChallengesTest extends TestCase
{
    public function testSelectMotherInitialsChallenge()
    {
        $challenge = new SelectMotherInitialsChallenge(
            $attempts = 3,
            $cpf = '12345678901',
            $choices = ['ABC', 'DEF']
        );

        $this->assertSame('select_mother_initials', $challenge->getName());
        $this->assertSame($attempts, $challenge->getAttemptsLeft());
        $this->assertSame($cpf, $challenge->getCpf());
        $this->assertSame($choices, $challenge->getChoices());
    }

    public function testTypeBirthdayChallenge()
    {
        $challenge = new TypeBirthdayChallenge($attempts = 3, $cpf = '12345678901');

        $this->assertSame('type_birthday', $challenge->getName());
        $this->assertSame($attempts, $challenge->getAttemptsLeft());
        $this->assertSame($cpf, $challenge->getCpf());
    }

    public function testTypeMotherInitialsChallenge()
    {
        $challenge = new TypeMotherInitialsChallenge($attempts = 3, $cpf = '12345678901');

        $this->assertSame('type_mother_initials', $challenge->getName());
        $this->assertSame($attempts, $challenge->getAttemptsLeft());
        $this->assertSame($cpf, $challenge->getCpf());
    }

    public function testTypePostalCodeChallenge()
    {
        $challenge = new TypePostalCodeChallenge($attempts = 3, $cpf = '12345678901');

        $this->assertSame('type_postal_code', $challenge->getName());
        $this->assertSame($attempts, $challenge->getAttemptsLeft());
        $this->assertSame($cpf, $challenge->getCpf());
    }

    public function testTypeVoterRegistrationChallenge()
    {
        $challenge = new TypeVoterRegistrationChallenge($attempts = 3, $cpf = '12345678901');

        $this->assertSame('type_voter_registration', $challenge->getName());
        $this->assertSame($attempts, $challenge->getAttemptsLeft());
        $this->assertSame($cpf, $challenge->getCpf());
    }
}
