<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\CityRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckDeployEventSubscriber implements EventSubscriberInterface
{
    const CHECK_DEPLOY_KEY = 'check_deploy';

    /** @var CacheProvider */
    private $cache;

    /** @var CityRepository */
    private $cityRepository;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var string */
    private $defaultClientUid;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'checkDeploy'];
    }

    public function __construct(EntityManagerInterface $em, $defaultClientUid)
    {
        $this->cityRepository = $em->getRepository('LoginCidadaoCoreBundle:City');
        $this->clientRepository = $em->getRepository('LoginCidadaoOAuthBundle:Client');
        $this->defaultClientUid = $defaultClientUid;

        $this->cache = null;
    }

    /**
     * @param CacheProvider $cache
     */
    public function setCacheProvider(CacheProvider $cache = null)
    {
        $this->cache = $cache;
    }

    public function checkDeploy()
    {
        if (null === $this->cache || $this->cache->contains(self::CHECK_DEPLOY_KEY)) {
            return;
        }

        $clients = $this->clientRepository->countClients();
        $cities = $this->cityRepository->countCities();
        $hasDefaultClient = $this->clientRepository->findOneBy(['uid' => $this->defaultClientUid]) instanceof Client;

        if ($clients <= 0 || $cities <= 0 || !$hasDefaultClient) {
            $this->cache->delete(self::CHECK_DEPLOY_KEY);
            throw new \RuntimeException('Make sure you did run the populate database command.');
        } else {
            $this->cache->save(self::CHECK_DEPLOY_KEY, true);
        }
    }
}
