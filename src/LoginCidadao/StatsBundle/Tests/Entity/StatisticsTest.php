<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Tests\Entity;

use LoginCidadao\StatsBundle\Entity\Statistics;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
    public function testStatistics()
    {
        $date = '2018-01-01';
        $time = '01:02:03';
        $index = 'index';
        $key = 'key';
        /** @var \DateTime $timestamp */
        $timestamp = \DateTime::createFromFormat('Y-m-d H:i:s', "{$date} {$time}");
        $value = 321;

        $statistics = (new Statistics())
            ->setIndex($index)
            ->setKey($key)
            ->setTimestamp($timestamp)
            ->setValue($value);

        $this->assertNull($statistics->getId());
        $this->assertSame($index, $statistics->getIndex());
        $this->assertSame($key, $statistics->getKey());
        $this->assertSame($timestamp, $statistics->getTimestamp());
        $this->assertSame($value, $statistics->getValue());
        $this->assertSame($date, $statistics->getDate());
        $this->assertSame("{$date} {$time}", $statistics->getDateTime());
    }
}
