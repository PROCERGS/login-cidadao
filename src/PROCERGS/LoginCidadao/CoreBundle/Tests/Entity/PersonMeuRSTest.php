<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Tests\Entity;

use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;

class PersonMeuRSTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $personMeuRS = (new PersonMeuRS())
            ->setId($id = '123')
            ->setPerson($person = new Person())
            ->setNfgAccessToken($nfgToken = 'nfg token')
            ->setNfgProfile($nfgProfile = new NfgProfile())
            ->setVoterRegistration($voterRegistration = '0123456789');

        $this->assertSame($id, $personMeuRS->getId());
        $this->assertSame($person, $personMeuRS->getPerson());
        $this->assertSame($nfgToken, $personMeuRS->getNfgAccessToken());
        $this->assertSame($nfgProfile, $personMeuRS->getNfgProfile());
        $this->assertSame($voterRegistration, $personMeuRS->getVoterRegistration());
    }
}
