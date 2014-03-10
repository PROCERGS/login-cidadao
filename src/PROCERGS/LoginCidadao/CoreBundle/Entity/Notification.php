<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\CoreBundle\Entity\NotificationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"simple" = "Notification", "interactive" = "InteractiveNotification"})
 */
class Notification implements NotificationInterface
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
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="shortText", type="string", length=255)
     */
    private $shortText;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="readDate", type="datetime", nullable=true)
     */
    private $readDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRead", type="boolean")
     */
    private $isRead;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\OAuthBundle\Entity\Client", inversedBy="notifications")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="notifications")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $person;

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
    private function setReadDate($readDate)
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
     * Set isRead
     *
     * @param boolean $isRead
     * @return Notification
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
        if (!$isRead) {
            $this->setReadDate(null);
        }

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    public function wasRead()
    {
        return $this->getIsRead();
    }

    public function getRead()
    {
        return $this->getIsRead();
    }

    public function setRead($isRead)
    {
        return $this->setIsRead($isRead);
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
        $client = $this->getClient();
        foreach ($auths as $auth) {
            if ($auth->getClient()->getId() === $client->getId()) {
                return true;
            }
        }
        return false;
    }

    public function checkSender()
    {
        return $this->getClient()->getMaxNotificationLevel() >= $this->getLevel();
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
        $this->setIsRead(false);
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

}
