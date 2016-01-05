<?php

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BackupCode
 *
 * @ORM\Table(name="backup_code")
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\BackupCodeRepository")
 */
class BackupCode
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="used", type="boolean")
     */
    private $used;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="backupCodes")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * @var type
     */
    private $person;

    public function __construct()
    {
        $this->setUsed(false);
    }

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
     * Set code
     *
     * @param string $code
     * @return BackupCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set used
     *
     * @param boolean $used
     * @return BackupCode
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used
     *
     * @return boolean
     */
    public function getUsed()
    {
        return $this->used;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;
        return $this;
    }

}
