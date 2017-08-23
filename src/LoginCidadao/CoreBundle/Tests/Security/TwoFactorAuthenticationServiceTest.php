<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Security;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\BackupCode;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\TwoFactorAuthenticationService;
use Google\Authenticator\GoogleAuthenticator as Google;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class TwoFactorAuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testEnable()
    {
        $person = $this->getPerson();

        $em = $this->getEntityManager();
        $em->expects($this->exactly(11))->method('persist'); // 11 times: 10 BackupCodes and 1 PersonInterface
        $em->expects($this->once())->method('flush');

        $google = new Google();
        $twoFactor = new TwoFactorAuthenticationService($em, $this->getGoogleAuthenticator($google));

        $secret = $twoFactor->generateSecret();
        $person->expects($this->once())->method('getGoogleAuthenticatorSecret')
            ->willReturn($secret);

        $twoFactor->enable($person, $google->getCode($secret));
    }

    public function testEnableWrongCode()
    {
        $this->setExpectedException('\InvalidArgumentException');

        /** @var \PHPUnit_Framework_MockObject_MockObject|PersonInterface $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $twoFactor = new TwoFactorAuthenticationService($this->getEntityManager(), $this->getGoogleAuthenticator());
        $person->expects($this->once())->method('getGoogleAuthenticatorSecret')
            ->willReturn($twoFactor->generateSecret());
        $twoFactor->enable($person, 'WRONG');
    }

    public function testDisable()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('setGoogleAuthenticatorSecret')->with(null);
        $person->expects($this->once())->method('getBackupCodes')->willReturn($this->getBackupCodes($person));

        $em = $this->getEntityManager();
        $em->expects($this->exactly(10))->method('remove')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\Entity\BackupCode'));
        $em->expects($this->once())->method('persist')->with($person);
        $em->expects($this->once())->method('flush');

        $twoFactor = new TwoFactorAuthenticationService($em, $this->getGoogleAuthenticator());
        $this->assertTrue($twoFactor->disable($person));
    }

    public function testGenerateBackupCodes()
    {
        $person = $this->getPerson();

        $em = $this->getEntityManager();
        $em->expects($this->exactly(10))->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\Entity\BackupCode'));

        $twoFactor = new TwoFactorAuthenticationService($em, $this->getGoogleAuthenticator());
        $backupCodes = $twoFactor->generateBackupCodes($person);

        foreach ($backupCodes as $backupCode) {
            $this->assertEquals($person, $backupCode->getPerson());
            $this->assertNotNull($backupCode->getCode());
        }
    }

    public function testRemoveBackupCodes()
    {
        $person = $this->getPerson();

        $person->expects($this->once())->method('getBackupCodes')->willReturn($this->getBackupCodes($person));

        $em = $this->getEntityManager();
        $em->expects($this->exactly(10))->method('remove')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\Entity\BackupCode'));

        $twoFactor = new TwoFactorAuthenticationService($em, $this->getGoogleAuthenticator());
        $twoFactor->removeBackupCodes($person);
    }

    public function testGetSecretUrl()
    {
        $url = 'https://secret.url';
        $person = $this->getPerson();

        $googleAuthClass = 'Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator';
        $googleAuth = $this->getMockBuilder($googleAuthClass)->disableOriginalConstructor()->getMock();
        $googleAuth->expects($this->once())->method('getUrl')->with($person)->willReturn($url);

        $twoFactor = new TwoFactorAuthenticationService($this->getEntityManager(), $googleAuth);
        $this->assertEquals($url, $twoFactor->getSecretUrl($person));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface');
    }

    private function getGoogleAuthenticator($googleAuth = null)
    {
        if (!$googleAuth) {
            $googleAuth = new Google();
        }

        return new GoogleAuthenticator($googleAuth, 'server', 'issuer');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonInterface
     */
    private function getPerson()
    {
        return $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @param PersonInterface $person
     * @return BackupCode[]
     */
    private function getBackupCodes(PersonInterface $person)
    {
        $backupCodes = [];
        while (count($backupCodes) < 10) {
            $backupCodes[] = (new BackupCode())
                ->setPerson($person)
                ->setCode(random_int(1111, 9999));
        }

        return $backupCodes;
    }
}
