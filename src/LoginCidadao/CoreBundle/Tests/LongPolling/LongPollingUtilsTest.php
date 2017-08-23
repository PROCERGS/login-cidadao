<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\LongPolling;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\LongPolling\LongPollingUtils;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @group time-sensitive
 */
class LongPollingUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testRunTimeLimited()
    {
        $timer = 'timeLimited';
        $stopwatch = new Stopwatch();
        $stopwatch->start($timer);

        $longPolling = new LongPollingUtils($this->getEntityManager(), 60);

        $count = 5;
        $response = $longPolling->runTimeLimited(function () use (&$count) {
            return --$count <= 0;
        });
        $duration = $stopwatch->stop($timer)->getDuration();

        $this->assertTrue($response);
        $this->assertEquals(0, $count);
        $this->assertLessThan(60, $duration);
    }

    public function testTimeout()
    {
        $this->setExpectedException('LoginCidadao\APIBundle\Exception\RequestTimeoutException');

        $longPolling = new LongPollingUtils($this->getEntityManager(), 30);
        $longPolling->runTimeLimited(function () {
            return false;
        });
    }

    public function testEntityUpdateCheckerCallback()
    {
        $updatedAt = new \DateTime('-1 hour');
        $currentUpdatedAt = new \DateTime('-1 hour');

        $person = $this->getPerson();
        $person->expects($this->any())->method('getUpdatedAt')
            ->willReturnCallback(function () use (&$currentUpdatedAt) {
                return $currentUpdatedAt;
            });

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')
            ->willReturn($this->getEvolvingPersonRepo($person, $currentUpdatedAt));

        $longPolling = new LongPollingUtils($em);
        $callback = $longPolling->getEntityUpdateCheckerCallback($person, $updatedAt);

        call_user_func($callback);
        $response = call_user_func($callback);

        $this->assertNotFalse($response);
        $this->assertNotEquals($updatedAt, $currentUpdatedAt);
        $this->assertEquals($person, $response);
    }

    public function testEntityUpdateCheckerCallbackNeverUpdated()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('getUpdatedAt')->willReturn(null);

        $repo = $this->getPersonRepository();
        $repo->expects($this->any())->method('find')->willReturn($person);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        $longPolling = new LongPollingUtils($em);
        $callback = $longPolling->getEntityUpdateCheckerCallback($person, new \DateTime());
        $response = call_user_func($callback);

        $this->assertFalse($response);
    }

    public function testObviouslyValidEmail()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('getEmailConfirmedAt')->willReturn(new \DateTime());

        $longPolling = new LongPollingUtils($this->getEntityManager());
        $response = $longPolling->waitValidEmail($person, new \DateTime());

        $this->assertTrue($response);
    }

    public function testWaitValidEmail()
    {
        $updatedAt = new \DateTime('-1 hour');
        $currentUpdatedAt = new \DateTime('-1 hour');

        $person = $this->getPerson();
        $person->expects($this->any())->method('getUpdatedAt')
            ->willReturnCallback(function () use (&$currentUpdatedAt) {
                return $currentUpdatedAt;
            });

        $repo = $this->getEvolvingPersonRepo($person, $currentUpdatedAt);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        $longPolling = new LongPollingUtils($em);
        $response = $longPolling->waitValidEmail($person, $updatedAt);

        $this->assertTrue($response);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonRepository
     */
    private function getPersonRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\PersonRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }

    /**
     * @return PersonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPerson()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|PersonInterface $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->any())->method('getId')->willReturn(123);

        return $person;
    }

    /**
     * @param PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person
     * @param \DateTime|null $currentUpdatedAt
     * @return PersonRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvolvingPersonRepo(&$person, &$currentUpdatedAt)
    {
        $repo = $this->getPersonRepository();
        $repo->expects($this->at(0))->method('find')->willReturn($person);
        $repo->expects($this->at(1))->method('find')
            ->willReturnCallback(function () use (&$person, &$currentUpdatedAt) {
                $currentUpdatedAt = new \DateTime();
                $person->expects($this->any())->method('getEmailConfirmedAt')->willReturn($currentUpdatedAt);

                return $person;
            });

        return $repo;
    }
}
