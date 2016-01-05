<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Notification
 *
 * @ORM\Table(name="notification", indexes={@ORM\Index(name="total_idx1", columns={"person_id", "read_date"}), @ORM\Index(name="total_idx2", columns={"person_id", "client_id", "read_date"})})
 * @ORM\Entity(repositoryClass="LoginCidadao\NotificationBundle\Entity\NotificationRepository")
 * @JMS\ExclusionPolicy("all")
 * @ORM\HasLifecycleCallbacks
 */
class Notification implements NotificationInterface
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
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @ORM\Column(name="icon", type="string", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     */
    private $icon;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @ORM\Column(name="title", type="string", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @ORM\Column(name="short_text", type="string", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     * @JMS\SerializedName("shortText")
     */
    private $shortText;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="callback_url", type="string", length=255, nullable=true)
     * @JMS\Groups({"form"})
     */
    private $callbackUrl;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Expose
     * @JMS\Groups({"public"})
     * @JMS\SerializedName("createdAt")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_date", type="datetime", nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $readDate;

    /**
     * @var int
     *
     * @deprecated since version 1.0.2
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="notifications")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     * @JMS\MaxDepth(1)
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="notifications")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     * @JMS\MaxDepth(1)
     */
    private $sender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expire_date", type="datetime", nullable=true)
     * @JMS\Groups({"form"})
     */
    private $expireDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="consider_read_date", type="datetime", nullable=true)
     * @JMS\Groups({"form"})
     */
    private $considerReadDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="received_date", type="datetime", nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $receivedDate;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="notifications")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @JMS\Expose
     * @JMS\Groups({"public", "form"})
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="html_template", type="text", nullable=true)
     */
    private $htmlTemplate;
    
    private $placeholders;
    private $mailTemplate;

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
     * Set icon
     *
     * @param string $icon
     * @return Notification
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set shortText
     *
     * @param string $shortText
     * @return Notification
     */
    public function setShortText($shortText)
    {
        $this->shortText = $shortText;

        return $this;
    }

    /**
     * Get shortText
     *
     * @return string
     */
    public function getShortText()
    {
        return $this->shortText;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Notification
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Notification
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
     * Set readDate
     *
     * @param \DateTime $readDate
     * @return Notification
     */
    public function setReadDate($readDate)
    {
        $this->readDate = $readDate;

        return $this;
    }

    /**
     * Get readDate
     *
     * @return \DateTime
     */
    public function getReadDate()
    {
        return $this->readDate;
    }

    /**
     * @JMS\Groups({"public"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("isRead")
     */
    public function isRead()
    {
        return (null !== $this->readDate);
    }

    public function wasRead()
    {
        return $this->getIsRead();
    }

    public function getRead()
    {
        return $this->isRead();
    }

    public function setRead($isRead)
    {
        if ($isRead) {
            $this->setReadDate(new \DateTime());
        } else {
            $this->setReadDate(null);
        }
        return $this;
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

    public function checkReceiver()
    {
        $auths = $this->getPerson()->getAuthorizations();        
        foreach ($auths as $auth) {            
            if ($auth->getClient()->getId() === $this->getSender()->getId()) {
                return true;
            }
        }
        return false;
    }

    public function checkSender()
    {
        return $this->getSender()->getMaxNotificationLevel() >= $this->getLevel();
    }

    public function canBeSent()
    {
        return $this->checkReceiver() && $this->checkSender();
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        switch ($level) {
            case self::LEVEL_NORMAL:
            case self::LEVEL_IMPORTANT:
            case self::LEVEL_EXTREME:
                $this->level = $level;
                break;
            default:
                throw new \Symfony\Component\Validator\Exception\InvalidArgumentException();
        }
        return $this;
    }

    public function __construct()
    {
        $this->setLevel(self::LEVEL_NORMAL);
        $this->setCreatedAt(new \DateTime());
    }

    public function isGlyphicon()
    {
        return (strstr($this->getIcon(), 'glyphicon') !== false);
    }

    public function isExtreme()
    {
        return $this->getLevel() === self::LEVEL_EXTREME;
    }

    public function setHtmlTemplate($var)
    {
        $this->htmlTemplate = $var;
        return $this;
    }

    public function getHtmlTemplate()
    {
        return $this->htmlTemplate;
    }

    public function parseHtmlTemplate($var)
    {
        $cplaces = array('%title%' => $this->title, '%shorttext%' => $this->shortText);
        foreach ($cplaces as $search => $replace) {
            $var = str_replace($search, $replace, $var);
        }
        return $this->setHtmlTemplate($var);
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function getConsiderReadDate()
    {
        return $this->considerReadDate;
    }

    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    public function setExpireDate(\DateTime $expireDate = null)
    {
        $this->expireDate = $expireDate;
        return $this;
    }

    public function setConsiderReadDate(\DateTime $considerReadDate = null)
    {
        $this->considerReadDate = $considerReadDate;
        return $this;
    }

    public function setReceivedDate(\DateTime $receivedDate = null)
    {
        $this->receivedDate = $receivedDate;
        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
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

    public function renderHtml()
    {
        return $this->htmlTemplate;
    }
    
    public function setPlaceholders($var)
    {
        $this->placeholders = $var;
    }
    
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
    
    public function setMailTemplate($var)
    {
        $this->mailTemplate = $var;
    }
    
    public function getMailTemplate()
    {
        return $this->mailTemplate;
    }
    
    
}
