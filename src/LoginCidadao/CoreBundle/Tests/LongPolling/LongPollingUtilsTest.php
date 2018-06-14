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
use LoginCidadao\APIBundle\Exception\RequestTimeoutException;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\LongPolling\LongPollingUtils;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @group time-sensitive
 */
class LongPollingUtilsTest extends TestCase
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
        $this->expectException(RequestTimeoutException::class);

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

        $person = new Person();
        $person->setUpdatedAt($updatedAt);

        $repo = $this->getEvolvingPersonRepoWithRealPerson($person, $currentUpdatedAt);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        $longPolling = new LongPollingUtils($em);
        $response = $longPolling->waitValidEmail($person, $updatedAt);

        $this->assertTrue($response);
    }

    /**
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        $em = $this->createMock(EntityManagerInterface::class);

        return $em;
    }

    /**
     * @return MockObject|PersonRepository
     */
    private function getPersonRepository()
    {
        $repo = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }

    /**
     * @return PersonInterface|MockObject
     */
    private function getPerson()
    {
        /** @var MockObject|PersonInterface $person */
        $person = $this->createMock(PersonInterface::class);
        $person->expects($this->any())->method('getId')->willReturn(123);

        return $person;
    }

    /**
     * @param PersonInterface|MockObject $person
     * @param \DateTime|null $currentUpdatedAt
     * @return PersonRepository|MockObject
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

    /**
     * @param PersonInterface $person
     * @param $currentUpdatedAt
     * @return PersonRepository|MockObject
     */
    private function getEvolvingPersonRepoWithRealPerson(PersonInterface &$person, &$currentUpdatedAt)
    {
        $repo = $this->getPersonRepository();
        $repo->expects($this->at(0))->method('find')->willReturn($person);
        $repo->expects($this->at(1))->method('find')
            ->willReturnCallback(function () use (&$person, &$currentUpdatedAt) {
                $person->setUpdatedAt($currentUpdatedAt);
                $person->setEmailConfirmedAt($currentUpdatedAt);

                return $person;
            });

        return $repo;
    }
}
