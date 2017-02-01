<?php

namespace LoginCidadao\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

/**
 * ActionLog
 *
 * @ORM\Table(
 *      name="action_log",
 *      indexes={
 *          @ORM\Index(name="idx_action_log_type", columns={"action_type"}),
 *          @ORM\Index(name="idx_action_log_access_token", columns={"access_token"}),
 *          @ORM\Index(name="idx_action_log_controller", columns={"controller"}),
 *          @ORM\Index(name="idx_action_log_action", columns={"action"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="LoginCidadao\APIBundle\Entity\ActionLogRepository")
 */
class ActionLog
{
    const TYPE_SELECT        = 'SELECT';
    const TYPE_UPDATE        = 'UPDATE';
    const TYPE_CREATE        = 'CREATE';
    const TYPE_DELETE        = 'DELETE';
    const TYPE_LOGIN         = 'LOGIN';
    const TYPE_IMPERSONATE   = 'IMPERSONATE';
    const TYPE_DEIMPERSONATE = 'DEIMPERSONATE';
    const TYPE_PROFILE_VIEW  = 'PROFILE_VIEW';

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
     * @ORM\Column(name="access_token", type="string", length=255, nullable=true)
     */
    private $accessToken;

    /**
     * OAuth Client id or impersonating user id when action type is TYPE_IMPERSONATE
     *
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer", nullable=true)
     */
    private $clientId;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=255)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=10)
     */
    private $method;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="text")
     */
    private $uri;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=45)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="audit_username", type="string", length=255)
     */
    private $auditUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="action_type", type="string", length=20)
     */
    private $actionType;

    /**
     * @var string
     *
     * @ORM\Column(name="response_code", type="integer", nullable=true)
     */
    private $responseCode;

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
     * Set accessToken
     *
     * @param string $accessToken
     * @return ActionLog
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set client id
     *
     * @param integer $clientId
     * @return ActionLog
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get client id
     *
     * @return integer
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set user id
     *
     * @param integer $userId
     * @return ActionLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get user id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return ActionLog
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return ActionLog
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return ActionLog
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ActionLog
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
     * Set uri
     *
     * @param string $uri
     * @return ActionLog
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return ActionLog
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set auditUsername
     *
     * @param string $auditUsername
     * @return ActionLog
     */
    public function setAuditUsername($auditUsername)
    {
        $this->auditUsername = $auditUsername;

        return $this;
    }

    /**
     * Get auditUsername
     *
     * @return string
     */
    public function getAuditUsername()
    {
        return $this->auditUsername;
    }

    /**
     * Set actionType
     *
     * @param string $actionType
     * @return ActionLog
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set response code
     *
     * @param integer $responseCode
     * @return ActionLog
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get response code
     *
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->responseCode;
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

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     * @return ActionLog
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }
}
