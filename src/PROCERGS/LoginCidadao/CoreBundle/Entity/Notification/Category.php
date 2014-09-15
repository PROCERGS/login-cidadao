<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use PROCERGS\LoginCidadao\CoreBundle\Model\CategoryInterface;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Notification;
use JMS\Serializer\Annotation as JMS;

/**
 * Category
 *
 * @ORM\Table()
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 */
class Category implements CategoryInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="default_icon", type="string", length=255)
     */
    private $defaultIcon;

    /**
     * @var string
     *
     * @ORM\Column(name="default_title", type="string", length=255)
     */
    private $defaultTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="default_short_text", type="string", length=255)
     */
    private $defaultShortText;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_template", type="text")
     */
    private $mailTemplate;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_sender_address", type="string", length=255)
     */
    private $mailSenderAddress;

    /**
     * @var boolean
     *
     * @ORM\Column(name="emailable", type="boolean")
     */
    private $emailable;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="PROCERGS\OAuthBundle\Entity\Client", inversedBy="categories")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="html_template", type="text", nullable=true)
     */
    private $htmlTemplate;

    /**
     * @var string
     *
     * @ORM\Column(name="markdown_template", type="text")
     */
    private $markdownTemplate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Notification", mappedBy="category")
     */
    protected $notifications;
    
    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\PersonNotificationOption", mappedBy="category") 
     */
    protected $personNotificationOption;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Placeholder", mappedBy="category")
     */
    protected $placeholders;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->placeholders = new ArrayCollection();
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
     * Set defaultIcon
     *
     * @param string $defaultIcon
     * @return Category
     */
    public function setDefaultIcon($defaultIcon)
    {
        $this->defaultIcon = $defaultIcon;

        return $this;
    }

    /**
     * Get defaultIcon
     *
     * @return string
     */
    public function getDefaultIcon()
    {
        return $this->defaultIcon;
    }

    /**
     * Set defaultTitle
     *
     * @param string $defaultTitle
     * @return Category
     */
    public function setDefaultTitle($defaultTitle)
    {
        $this->defaultTitle = $defaultTitle;

        return $this;
    }

    /**
     * Get defaultTitle
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return $this->defaultTitle;
    }

    /**
     * Set defaultShortText
     *
     * @param string $defaultShortText
     * @return Category
     */
    public function setDefaultShortText($defaultShortText)
    {
        $this->defaultShortText = $defaultShortText;

        return $this;
    }

    /**
     * Get defaultShortText
     *
     * @return string
     */
    public function getDefaultShortText()
    {
        return $this->defaultShortText;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mailTemplate
     *
     * @param string $mailTemplate
     * @return Category
     */
    public function setMailTemplate($mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;

        return $this;
    }

    /**
     * Get mailTemplate
     *
     * @return string
     */
    public function getMailTemplate()
    {
        return $this->mailTemplate;
    }

    /**
     * Set mailSenderAddress
     *
     * @param string $mailSenderAddress
     * @return Category
     */
    public function setMailSenderAddress($mailSenderAddress)
    {
        $this->mailSenderAddress = $mailSenderAddress;

        return $this;
    }

    /**
     * Get mailSenderAddress
     *
     * @return string
     */
    public function getMailSenderAddress()
    {
        return $this->mailSenderAddress;
    }

    /**
     * Set emailable
     *
     * @param boolean $emailable
     * @return Category
     */
    public function setEmailable($emailable)
    {
        $this->emailable = $emailable;

        return $this;
    }

    /**
     * Get emailable
     *
     * @return boolean
     */
    public function getEmailable()
    {
        return $this->emailable;
    }

    /**
     * Set application
     *
     * @param string $application
     * @return Category
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application
     *
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set htmlTemplate
     *
     * @param string $htmlTemplate
     * @return Category
     */
    public function setHtmlTemplate($htmlTemplate)
    {
        $this->htmlTemplate = $htmlTemplate;

        return $this;
    }

    /**
     * Get htmlTemplate
     *
     * @return string
     */
    public function getHtmlTemplate()
    {
        return $this->htmlTemplate;
    }

    /**
     * Set markdownTemplate
     *
     * @param string $markdownTemplate
     * @return Category
     */
    public function setMarkdownTemplate($markdownTemplate)
    {
        $this->markdownTemplate = $markdownTemplate;

        return $this;
    }

    /**
     * Get markdownTemplate
     *
     * @return string
     */
    public function getMarkdownTemplate()
    {
        return $this->markdownTemplate;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function setNotifications(ArrayCollection $notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     *
     * @return ArrayCollection
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     *
     * @return array
     */
    public function getPlaceholdersArray(array $parameters)
    {
        $result = array();
        foreach ($this->getPlaceholders() as $placeholder) {
            $result[$placeholder->getName()] = $placeholder->getDefault();
        }
        return array_merge($result, $parameters);
    }

    public function setPlaceholders(ArrayCollection $placeholders)
    {
        $this->placeholders = $placeholders;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }
    
    public function getPersonNotificationOption()
    {
        return $this->personNotificationOption;
    }
    
    public function setPersonNotificationOption($var)
    {
        $this->personNotificationOption = $var;
        return $this;
    }
    
}
