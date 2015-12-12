<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use LoginCidadao\NotificationBundle\Entity\Notification;
use LoginCidadao\NotificationBundle\Model\NotificationInterface;

/**
 * FailedCallback
 *
 * @ORM\Table(name="failed_callback")
 * @ORM\Entity(repositoryClass="LoginCidadao\NotificationBundle\Entity\FailedCallbackRepository")
 */
class FailedCallback
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="request_url", type="string", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $requestUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="response_code", type="integer", length=255)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $responseCode;

    /**
     * @var string
     *
     * @ORM\Column(name="response_body", type="text", nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $responseBody;

    /**
     * @var NotificationInterface
     *
     * @ORM\ManyToOne(targetEntity="Notification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $notification;

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
     * Set date
     *
     * @param \DateTime $date
     * @return FailedCallback
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set requestUrl
     *
     * @param string $requestUrl
     * @return FailedCallback
     */
    public function setRequestUrl($requestUrl)
    {
        $this->requestUrl = $requestUrl;

        return $this;
    }

    /**
     * Get requestUrl
     *
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * Set responseCode
     *
     * @param string $responseCode
     * @return FailedCallback
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get responseCode
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set responseBody
     *
     * @param string $responseBody
     * @return FailedCallback
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;

        return $this;
    }

    /**
     * Get responseBody
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param NotificationInterface $notification
     * @return \LoginCidadao\NotificationBundle\Entity\FailedCallback
     */
    public function setNotification(NotificationInterface $notification)
    {
        $this->notification = $notification;
        return $this;
    }

}
