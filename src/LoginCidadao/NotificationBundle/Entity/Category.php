<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\NotificationBundle\Entity\Notification;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\CoreBundle\Model\UniqueEntityInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Michelf\MarkdownExtra;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="LoginCidadao\NotificationBundle\Entity\CategoryRepository")
 * @JMS\ExclusionPolicy("all")
 */
class Category implements CategoryInterface, UniqueEntityInterface
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
     * @Assert\NotBlank()
     * @Assert\Length(max = "255")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="default_icon", type="string", length=255)
     */
    private $defaultIcon;

    /**
     * @ORM\Column(name="default_title", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max = "255")
     */
    private $defaultTitle;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max = "255")
     * @ORM\Column(name="default_short_text", type="string", length=255)
     */
    private $defaultShortText;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_template", type="text", nullable=true)
     */
    private $mailTemplate;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_sender_address", type="string", length=255, nullable=true)
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
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="categories")
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
    private $markdownTemplate = '%shortText%';

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\Notification", mappedBy="category")
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\PersonNotificationOption", mappedBy="category")
     */
    protected $personNotificationOptions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\Placeholder", mappedBy="category")
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
     */
    public function setDefaultTitle($defaultTitle)
    {
        $this->defaultTitle = $defaultTitle;

        return $this;
    }

    /**
     * Get defaultTitle
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

    public function isEmailable()
    {
        return $this->getEmailable();
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
        if ($this->htmlTemplate === null) {
            $this->setHtmlTemplate(MarkdownExtra::defaultTransform($this->getMarkdownTemplate()));
        }
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

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    public function getPersonNotificationOptions()
    {
        return $this->personNotificationOptions;
    }

    public function setPersonNotificationOptions($var)
    {
        $this->personNotificationOptions = $var;
        return $this;
    }

    /* Unique Interface Stuff */

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    private $uid;

    /**
     * Gets the Unique Id of the Entity.
     * @return string the entity UID
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the Unique Id of the Entity.
     * @param string $id the entity UID
     * @return AbstractUniqueEntity
     */
    public function setUid($uid = null)
    {
        $this->uid = $uid;
        return $this;
    }

}
