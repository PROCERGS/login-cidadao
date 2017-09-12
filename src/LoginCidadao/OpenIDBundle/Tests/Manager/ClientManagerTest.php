<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Manager;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ClientManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClientById()
    {
        $id = 123;

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('find')->willReturn(new Client());

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoOAuthBundle:Client')
            ->willReturn($repo);

        $manager = new ClientManager($em, $this->getDispatcher());
        $manager->getClientById($id);
    }

    public function testGetClientByPublicId()
    {
        $id = '123_randomIdHere';

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('findOneBy')
            ->with(['id' => 123, 'randomId' => 'randomIdHere'])
            ->willReturn(new Client());

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoOAuthBundle:Client')
            ->willReturn($repo);

        $manager = new ClientManager($em, $this->getDispatcher());
        $manager->getClientById($id);
    }

    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @return ClientRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }

    /**
     * @return EventDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDispatcher()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }
}
