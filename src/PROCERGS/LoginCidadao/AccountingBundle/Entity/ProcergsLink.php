<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\OAuthBundle\Entity\Client;

/**
 * ProcergsLink
 *
 * @ORM\Table(name="client_procergs_link")
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProcergsLink
{
    const TYPE_INTERNAL = 'internal';
    const TYPE_EXTERNAL = 'external';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Client
     * @ORM\OneToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="system_type", type="string", length=255, nullable=false)
     */
    private $systemType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

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
     * Set client
     *
     * @param Client $client
     * @return ProcergsLink
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * @param string $systemType
     * @return ProcergsLink
     */
    public function setSystemType($systemType)
    {
        switch ($systemType) {
            case self::TYPE_EXTERNAL:
            case self::TYPE_INTERNAL:
                $this->systemType = $systemType;
                break;
            default:
                throw new \InvalidArgumentException("Invalid system type '$systemType'");
        }

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ProcergsLink
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ProcergsLink
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
        if (!$this->updatedAt) {
            $this->updatedAt = new \DateTime();
        }
    }
}
