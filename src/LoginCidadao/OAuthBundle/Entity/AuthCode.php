<?php

namespace LoginCidadao\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_code")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="scope",
 *              column=@ORM\Column(
 *              name     = "scope",
 *              type     = "string",
 *              length   = 1000,
 *              nullable = true
 *          )
 *      )
 * })
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="id_token", type="text", nullable=true)
     */
    protected $idToken;

    /**
     * @ORM\Column(name="session_id", type="string", length=255, nullable=true)
     */
    protected $sessionId;

    public function getIdToken()
    {
        return $this->idToken;
    }

    public function setIdToken($idToken)
    {
        $this->idToken = $idToken;
        return $this;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
