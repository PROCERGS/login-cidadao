<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

/**
 * @ORM\Entity
 * @ORM\Table(name="config_not_cli")
 * @ORM\HasLifecycleCallbacks
 */
class ConfigNotCli
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="icon", type="string", length=255)
     */
    protected $icon;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="mail_template", type="text")
     */
    protected $mailTemplate;

    /**
     * @ORM\Column(name="mail_domain", type="string")
     */
    protected $mailDomain;

    /**
     * @ORM\Column(name="mail_send", type="integer")
     */
    protected $mailSend;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\OAuthBundle\Entity\Client", inversedBy="configNotClis")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $client;
    
    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="configNotCli")
     */
    protected $notifications;

    /**
     * @ORM\Column(name="kind", type="string", length=18)
     */
    protected $kind;

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setName($var)
    {
        $this->name = $var;
        
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setClient($client)
    {
        $this->client = $client;
        
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setMailTemplate($var)
    {
        $this->mailTemplate = $var;
        
        return $this;
    }

    public function getMailTemplate()
    {
        return $this->mailTemplate;
    }

    public function setMailDomain($var)
    {
        $this->mailDomain = $var;
        
        return $this;
    }

    public function getMailDomain()
    {
        return $this->mailDomain;
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

    public function setKind($var)
    {
        $this->kind = $var;
        
        return $this;
    }

    public function getKind()
    {
        return $this->kind;
    }
    
    public function getNotifications()
    {
        return $this->notifications;
    }
    
}
