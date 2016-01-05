<?php

namespace LoginCidadao\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="refresh_token")
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
class RefreshToken extends BaseRefreshToken
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
     */
    protected $user;

    public function setExpired()
    {
        $now = new \DateTime();
        $this->setExpiresAt($now->getTimestamp());
    }

}
