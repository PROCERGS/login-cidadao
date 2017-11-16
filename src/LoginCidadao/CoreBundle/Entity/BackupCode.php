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
use LoginCidadao\CoreBundle\Model\PersonInterface;

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
     * @var PersonInterface
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

    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }

}
