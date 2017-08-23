<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\BackupCode;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class TwoFactorAuthenticationService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var GoogleAuthenticator */
    private $twoFactor;

    /**
     * TwoFactorAuthenticationService constructor.
     * @param EntityManagerInterface $em
     * @param GoogleAuthenticator $twoFactor
     */
    public function __construct(EntityManagerInterface $em, GoogleAuthenticator $twoFactor)
    {
        $this->em = $em;
        $this->twoFactor = $twoFactor;
    }

    public function enable(PersonInterface $person, $verificationCode)
    {
        if (!$this->twoFactor->checkCode($person, $verificationCode)) {
            throw new \InvalidArgumentException('Invalid code! Make sure you configured your app correctly and your smartphone\'s time is adjusted.');
        }

        $this->generateBackupCodes($person);
        $this->em->persist($person);
        $this->em->flush();

        return true;
    }

    public function disable(PersonInterface $person)
    {
        $this->removeBackupCodes($person);
        $person->setGoogleAuthenticatorSecret(null);

        $this->em->persist($person);
        $this->em->flush();

        return true;
    }

    public function generateSecret()
    {
        return $this->twoFactor->generateSecret();
    }

    public function getSecretUrl(PersonInterface $person)
    {
        return $this->twoFactor->getUrl($person);
    }

    /**
     * @param PersonInterface $person
     * @return BackupCode[]
     */
    public function generateBackupCodes(PersonInterface $person)
    {
        $backupCodes = [];
        while (count($backupCodes) < 10) {
            $code = bin2hex(random_bytes(5));
            $backupCode = new BackupCode();
            $backupCode->setPerson($person);
            $backupCode->setCode($code);
            $backupCodes[] = $backupCode;
            $this->em->persist($backupCode);
        }

        return $backupCodes;
    }

    public function removeBackupCodes(PersonInterface $person)
    {
        $backupCodes = $person->getBackupCodes();
        foreach ($backupCodes as $backupCode) {
            $this->em->remove($backupCode);
        }

        return true;
    }
}
