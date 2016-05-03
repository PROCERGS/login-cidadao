<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Statistics
 *
 * @ORM\Entity(repositoryClass="LoginCidadao\StatsBundle\Entity\StatisticsRepository")
 * @ORM\Table(name="statistics",indexes={
 *      @ORM\Index(name="idx_timestamp_index_key", columns={"stats_timestamp", "stats_index", "stats_key"}),
 *      @ORM\Index(name="idx_timestamp_index", columns={"stats_timestamp", "stats_index"}),
 *      @ORM\Index(name="idx_timestamp_key", columns={"stats_timestamp", "stats_key"}),
 *      @ORM\Index(name="idx_index", columns={"stats_index"}),
 *      @ORM\Index(name="idx_key", columns={"stats_key"}),
 * })
 */
class Statistics
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stats_timestamp", type="datetime")
     */
    private $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="stats_index", type="string", length=255)
     * @JMS\Groups({"date","datetime"})
     */
    private $index;

    /**
     * @var string
     *
     * @ORM\Column(name="stats_key", type="string", length=255)
     * @JMS\Groups({"date","datetime"})
     */
    private $key;

    /**
     * @var integer
     *
     * @ORM\Column(name="stats_value", type="integer")
     * @JMS\Groups({"date","datetime"})
     */
    private $value;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return Statistics
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set index
     *
     * @param string $index
     * @return Statistics
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get index
     *
     * @return string 
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return Statistics
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value
     *
     * @param integer $value
     * @return Statistics
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("timestamp")
     * @JMS\Groups({"date"})
     * @return string
     */
    public function getDate()
    {
        return $this->getTimestamp()->format('Y-m-d');
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("timestamp")
     * @JMS\Groups({"datetime"})
     * @return string
     */
    public function getDateTime()
    {
        return $this->getTimestamp()->format('Y-m-d H:i:s');
    }
}
