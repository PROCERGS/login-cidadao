<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

/**
 * @deprecated since version 1.0.2
 * @ ORM\Entity
 * @ ORM\Table(name="config_not_per")
 * @ ORM\HasLifecycleCallbacks
 */
class ConfigNotPer
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="mail_send", type="integer")
     */
    protected $mailSend;

    /**
     * @ORM\OneToOne(targetEntity="ConfigNotCli", inversedBy="configNotPer")
     * @ORM\JoinColumn(name="config_not_cli_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $configNotCli;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="configNotPers")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $person;


    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setConfigNotCli($var)
    {
        $this->configNotCli = $var;

        return $this;
    }

    public function getConfigNotCli()
    {
        return $this->configNotCli;
    }

    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setMailSend($var)
    {
        $this->mailSend = $var;

        return $this;
    }

    public function getMailSend()
    {
        return $this->mailSend;
    }



}
