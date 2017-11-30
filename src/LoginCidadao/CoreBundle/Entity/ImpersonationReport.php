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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ImpersonationReport
 *
 * @ORM\Table(name="impersonation_report")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\ImpersonationReportRepository")
 */
class ImpersonationReport
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="impersonator_id", referencedColumnName="id")
     */
    protected $impersonator;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id")
     */
    protected $target;

    /**
     * @Assert\Length(
     *      min = 15,
     *      minMessage = "admin.impersonation_report.validation.error.report.short"
     * )
     * @ORM\Column(name="report", type="text")
     */
    protected $report;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\APIBundle\Entity\ActionLog")
     * @ORM\JoinColumn(name="action_log_id", referencedColumnName="id")
     */
    protected $actionLog;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function getId()
    {
        return $this->id;
    }

    public function getImpersonator()
    {
        return $this->impersonator;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getReport()
    {
        return $this->report;
    }

    public function getActionLog()
    {
        return $this->actionLog;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setImpersonator($impersonator)
    {
        $this->impersonator = $impersonator;
        return $this;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }

    public function setActionLog($actionLog)
    {
        $this->actionLog = $actionLog;
        return $this;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
