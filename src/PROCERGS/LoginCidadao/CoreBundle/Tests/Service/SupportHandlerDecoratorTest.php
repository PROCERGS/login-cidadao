<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Tests\Service;

use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Model\IdentifiablePersonInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use LoginCidadao\SupportBundle\Service\SupportHandlerInterface;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Service\SupportHandlerDecorator;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;

class SupportHandlerDecoratorTest extends TestCase
{
    public function testGetThirdPartyConnections()
    {
        $identifiablePerson = $this->createMock(IdentifiablePersonInterface::class);
        $identifiablePerson->expects($this->once())->method('getId')->willReturn($id = 321);

        $person = $this->createMock(PersonInterface::class);

        $personRS = $this->createMock(PersonMeuRS::class);
        $personRS->expects($this->once())->method('getNfgAccessToken')->willReturn('123');

        $supportHandler = $this->createMock(SupportHandlerInterface::class);
        $supportHandler->expects($this->once())->method('getThirdPartyConnections')->with($person);

        $rsHelper = $this->createMock(MeuRSHelper::class);
        $rsHelper->expects($this->once())->method('getPersonMeuRS')->willReturn($personRS);
        $personRepo = $this->createMock(PersonRepository::class);
        $personRepo->expects($this->once())->method('find')->with($id)->willReturn($person);

        $decorator = new SupportHandlerDecorator($supportHandler, $rsHelper, $personRepo);
        $decorator->getThirdPartyConnections($identifiablePerson);
    }

    public function testDecorator()
    {
        $id = 123;
        $idPerson = $this->createMock(IdentifiablePersonInterface::class);
        $ticket = 'random_ticket';
        $supportPerson = $this->createMock(SupportPerson::class);

        $supportHandler = $this->createMock(SupportHandlerInterface::class);
        $supportHandler->expects($this->once())->method('getSupportPerson')->with($id);
        $supportHandler->expects($this->once())->method('getPhoneMetadata')->with($idPerson);
        $supportHandler->expects($this->once())->method('getInitialMessage')->with($ticket);
        $supportHandler->expects($this->once())->method('getValidationMap')->with($supportPerson);

        $decorator = new SupportHandlerDecorator($supportHandler, $this->getRsHelper(), $this->getPersonRepo());
        $decorator->getSupportPerson($id);
        $decorator->getPhoneMetadata($idPerson);
        $decorator->getInitialMessage($ticket);
        $decorator->getValidationMap($supportPerson);
    }

    private function getRsHelper()
    {
        return $this->createMock(MeuRSHelper::class);
    }

    private function getPersonRepo()
    {
        return $this->createMock(PersonRepository::class);
    }
}
