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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="short_text", type="string", length=255, nullable=true)
     */
    protected $shortText;    

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="mail_tpl", type="text")
     */
    protected $mailTpl;

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
    
    /**
     * @var string
     *
     * @ORM\Column(name="html_tpl", type="text", nullable=true)
     */
    private $htmlTpl;

    /**
     * @var string
     *
     * @ORM\Column(name="md_tpl", type="text", nullable=true)
     */
    private $mdTpl;
    
    /**
     * @ORM\OneToOne(targetEntity="ConfigNotPer", mappedBy="configNotCli")
     */
    protected $configNotPer;    

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

    public function setMailTpl($var)
    {
        $this->mailTpl = $var;
        
        return $this;
    }

    public function getMailTpl()
    {
        return $this->mailTpl;
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
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setShortText($shortText)
    {
        $this->shortText = $shortText;
        return $this;
    }
    
    public function getShortText()
    {
        return $this->shortText;
    }
    
    public function setMdTpl($var)
    {
        $this->mdTpl = $var;
        return $this;
    }
    
    public function getMdTpl()
    {
        return $this->mdTpl;
    }
    
    public function setHtmlTpl($var)
    {
        $this->htmlTpl = $var;
        return $this;
    }
    
    public function getHtmlTpl()
    {
        return $this->htmlTpl;
    }
    
    public function setConfigNotPer($var)
    {
        $this->configNotPer = $var;
        return $this;
    }
    
    public function getConfigNotPer()
    {
        return $this->configNotPer;
    }
    
}
