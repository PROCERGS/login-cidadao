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
use LoginCidadao\StatsBundle\Handler\StatsHandler;
use LoginCidadao\CoreBundle\Entity\Authorization;

class StatisticsSubscriber implements EventSubscriber
{
    /** @var StatsHandler */
    protected $statsHandler;

    public function setStatsHandler(StatsHandler $statsHandler)
    {
        $this->statsHandler = $statsHandler;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postRemove',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->authorizations($args);
        $this->authorizationsAggregate($args);
    }

    public function authorizations(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!($entity instanceof Authorization)) {
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
        } else {
            $count = 0;
        }

        if (!is_int($count)) {
            $count = 0;
        }

        return $count;
    }

    public function authorizationsAggregate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!($entity instanceof Authorization)) {
            return;
        }

        $em = $args->getEntityManager();
        $key = $entity->getClient()->getId();
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d 00:00:00'));
        $clientRepo = $em->getRepository('LoginCidadaoOAuthBundle:Client');
        $statsRepo = $em->getRepository('LoginCidadaoStatsBundle:Statistics');
        $count = $this->getClientCount($clientRepo, $entity);

        $statistics = $statsRepo->findOneBy(
            array(
                'timestamp' => $date,
                'index' => 'agg.client.users',
                'key' => $key,
            )
        );
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

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->authorizations($args);
        $this->authorizationsAggregate($args);
    }
}
