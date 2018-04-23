<?php
/*
 *  This file is part of the login-cidadao project or it's bundles.
 *
 *  (c) Guilherme Donato <guilhermednt on github>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\StatsBundle\Entity\Statistics;
use LoginCidadao\CoreBundle\Entity\Authorization;

class StatisticsSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return ['postPersist', 'postRemove'];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->authorizations($args);
        $this->authorizationsAggregate($args);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function authorizations(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Authorization) {
            return;
        }

        $em = $args->getEntityManager();
        $key = $entity->getClient()->getId();

        /** @var ClientRepository $clientRepo */
        $clientRepo = $em->getRepository('LoginCidadaoOAuthBundle:Client');

        $count = $this->getClientCount($clientRepo, $entity);

        $statistics = new Statistics();
        $statistics->setIndex('client.users')
            ->setKey($key)
            ->setTimestamp(new \DateTime())
            ->setValue($count);
        $em->persist($statistics);
        $em->flush($statistics);
    }

    private function getClientCount(ClientRepository $clientRepo, Authorization $entity)
    {
        $counts = $clientRepo->getCountPerson(
            $entity->getPerson(),
            $entity->getClient()->getId()
        );
        if (count($counts) > 0) {
            $count = $counts[0]['qty'];
            $count = is_int($count) ? $count : 0;
        } else {
            $count = 0;
        }

        return $count;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function authorizationsAggregate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!($entity instanceof Authorization)) {
            return;
        }

        $em = $args->getEntityManager();
        $key = $entity->getClient()->getId();
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d 00:00:00'));

        /** @var ClientRepository $clientRepo */
        $clientRepo = $em->getRepository('LoginCidadaoOAuthBundle:Client');
        $statsRepo = $em->getRepository('LoginCidadaoStatsBundle:Statistics');
        $count = $this->getClientCount($clientRepo, $entity);

        $statistics = $statsRepo->findOneBy([
            'timestamp' => $date,
            'index' => 'agg.client.users',
            'key' => $key,
        ]);
        if (!($statistics instanceof Statistics)) {
            $statistics = new Statistics();
            $statistics->setIndex('agg.client.users')
                ->setKey($key)
                ->setTimestamp($date);
            $em->persist($statistics);
        }
        $statistics->setValue($count);
        $em->flush($statistics);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->authorizations($args);
        $this->authorizationsAggregate($args);
    }
}
