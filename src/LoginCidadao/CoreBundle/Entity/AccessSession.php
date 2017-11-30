<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="access_session")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AccessSession
{

    /**
     *
     * @var integer @ORM\Column(name="id", type="integer")
     *      @ORM\Id
     *      @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var string @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     *
     * @var string @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;

    /**
     *
     * @var string @ORM\Column(name="dhacess", type="datetime")
     */
    private $dhacess;

    /**
     *
     * @var string @ORM\Column(name="val", type="integer")
     */
    private $val;

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($var)
    {
        $this->username = $var;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setIp($var)
    {
        $this->ip = $var;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIDhacess($var)
    {
        $this->dhacess = $var;

        return $this;
    }

    public function getIDhacess()
    {
        return $this->dhacess;
    }

    public function setVal($var)
    {
        $this->val = $var;

        return $this;
    }

    public function getVal()
    {
        if ($this->val === null) {
            $this->val = 0;
        }
        return $this->val;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function doStuffOnPrePersist()
    {
        $this->setIDhacess(new \DateTime());
    }

    public function fromArray($var)
    {
        if (isset($var['ip'])) {
            $this->ip = $var['ip'];
        }
        if (isset($var['username'])) {
            $this->username = $var['username'];
        }
    }
}
