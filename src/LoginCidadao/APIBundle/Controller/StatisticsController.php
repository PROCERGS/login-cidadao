<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Controller;

use Doctrine\Common\Cache\CacheProvider;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;

class StatisticsController extends BaseController
{

    const CACHE_PUBLIC_STATISTICS_API_KEY = 'api.public_statistics';
    const CACHE_LIFE_TIME = 60;

    /**
     * Gets the current authorization count for each public Client (service)
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the current authorization count for each public Client (service).",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="stats")
     * @throws NotFoundHttpException
     */
    public function getStatisticsAction()
    {
        $useCache = true;

        /** @var AuthorizationRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Authorization');
        $uid = $this->getParameter('oauth_default_client.uid');

        $fetchData = function () use ($repo, $uid) {
            return $repo->statsUsersByServiceVisibility(true, $uid);
        };

        if ($useCache) {
            $usersByService = $this->fetchCached(
                self::CACHE_PUBLIC_STATISTICS_API_KEY,
                $fetchData
            );
        } else {
            $usersByService = $fetchData();
        }

        $stats = [
            'users_by_service' => $usersByService,
        ];

        $view = $this->view($this->getTotalAndRemoveUid($stats, $uid))
            ->setSerializationContext(SerializationContext::create()->setSerializeNull(true));

        return $this->handleView($view);
    }

    private function fetchCached($id, callable $callback)
    {
        if (false === $this->has('cache')) {
            $this->log('warning', 'Serving public statistics without cache!');

            return $callback();
        }

        /** @var CacheProvider $cache */
        $cache = $this->get('cache');

        $stats = $cache->fetch($id);
        if (false === $stats) {
            $cache->save($id, $callback(), self::CACHE_LIFE_TIME);
        }

        $stats = $cache->fetch($id);
        if (!$stats) {
            $this->log('critical', 'Cache malfunction or unable to fetch statistics!');
        }

        return $stats;
    }

    private function log($level, $message)
    {
        if (false === $this->has('logger')) {
            return;
        }

        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');

        $logger->log($level, $message);
    }

    private function getTotalAndRemoveUid($results, $uid)
    {
        $total = 0;

        $results['users_by_service'] = array_map(
            function ($service) use (&$total, $uid) {
                if (array_key_exists('uid', $service) && $service['uid'] === $uid) {
                    $total = $service['users'];
                }
                unset($service['uid']);

                return $service;
            },
            $results['users_by_service']
        );
        $results['total_users'] = $total;

        return $results;
    }
}
