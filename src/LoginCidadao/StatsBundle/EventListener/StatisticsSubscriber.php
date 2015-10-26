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
use LoginCidadao\StatsBundle\Entity\Statistics;
use LoginCidadao\StatsBundle\Handler\StatsHandler;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;

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
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->authorizations($args);
    }

    public function authorizations(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!($entity instanceof Authorization)) {
            return;
        }

        $clientRepo = $args->getEntityManager()
            ->getRepository('PROCERGSOAuthBundle:Client');

        $counts = $clientRepo->getCountPerson($entity->getPerson(),
            $entity->getClient()->getId());
        $count  = reset($counts)['qty'] || 0;

        $statistics = new Statistics();
        $statistics->setIndex('client.users')
            ->setKey($entity->getClient()->getId())
            ->setTimestamp(new \DateTime())
            ->setValue($count)
        ;
        $args->getEntityManager()->persist($statistics);
        $args->getEntityManager()->flush($statistics);
    }
}
