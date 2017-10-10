<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\LogBundle\Handler;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\LogBundle\Entity\Log;
use Monolog\Handler\AbstractProcessingHandler;

class MonologDBHandler extends AbstractProcessingHandler
{
    const DISABLE_LOGGING_FLAG_KEY = 'db_logging_disabled';
    const LIFETIME = 1800;

    /** @var EntityManagerInterface */
    private $em;

    /** @var CacheProvider */
    private $cache;

    /**
     * MonologDBHandler constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * @param CacheProvider $cache
     */
    public function setCacheProvider(CacheProvider $cache = null)
    {
        $this->cache = $cache;
    }

    protected function write(array $record)
    {
        if ($this->cache !== null && $this->cache->contains(self::DISABLE_LOGGING_FLAG_KEY)) {
            return;
        }

        $logEntry = (new Log())
            ->setMessage($record['message'])
            ->setLevel($record['level'])
            ->setLevelName($record['level_name'])
            ->setExtra($record['extra'])
            ->setContext($record['context']);

        try {
            $this->em->persist($logEntry);
            $this->em->flush();
        } catch (\Exception $e) {
            if ($this->cache) {
                $this->cache->save(self::DISABLE_LOGGING_FLAG_KEY, true, self::LIFETIME);
            }
        }
    }
}
