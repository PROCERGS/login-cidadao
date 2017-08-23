<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\LongPolling;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\APIBundle\Exception\RequestTimeoutException;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Tests\LongPolling\LongPollableInterface;

class LongPollingUtils
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var int */
    private $maxExecutionTime;

    /**
     * LongPollingUtils constructor.
     * @param EntityManagerInterface $em
     * @param null $maxExecutionTime
     */
    public function __construct(EntityManagerInterface $em, $maxExecutionTime = null)
    {
        $this->em = $em;
        $this->maxExecutionTime = $maxExecutionTime;

        if ($maxExecutionTime === null) {
            $this->maxExecutionTime = ini_get('max_execution_time');
        }
    }

    public function runTimeLimited($callback, $waitTime = 1)
    {
        $maxExecutionTime = $this->maxExecutionTime;
        $limit = $maxExecutionTime ? $maxExecutionTime - 2 : 60;
        $startTime = time();
        while ($limit > 0) {
            $result = call_user_func($callback);
            $delta = time() - $startTime;

            if ($result !== false) {
                return $result;
            }

            $limit -= $delta;
            if ($limit <= 0) {
                break;
            }
            $startTime = time();
            sleep($waitTime);
        }
        throw new RequestTimeoutException("Request Timeout");
    }

    /**
     * @param PersonInterface $user
     * @param \DateTime $updatedAt
     * @return bool
     * @throws RequestTimeoutException
     */
    public function waitValidEmail(PersonInterface $user, \DateTime $updatedAt)
    {
        if ($user->getEmailConfirmedAt() instanceof \DateTime) {
            return true;
        }

        $person = $this->runTimeLimited(
            $this->getEntityUpdateCheckerCallback($user, $updatedAt)
        );

        return $person->getEmailConfirmedAt() instanceof \DateTime;
    }

    public function getEntityUpdateCheckerCallback(LongPollableInterface $entity, $updatedAt)
    {
        $id = $entity->getId();
        $em = $this->em;
        $repository = $em->getRepository(get_class($entity));

        return function () use ($em, $repository, $id, $updatedAt) {
            $em->clear();

            /** @var LongPollableInterface $entity */
            $entity = $repository->find($id);
            if (!$entity->getUpdatedAt()) {
                return false;
            }

            if ($entity->getUpdatedAt() > $updatedAt) {
                return $entity;
            }

            return false;
        };
    }
}
