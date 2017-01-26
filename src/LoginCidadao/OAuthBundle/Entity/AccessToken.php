<?php

namespace LoginCidadao\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OAuthBundle\Entity\AccessTokenRepository")
 * @ORM\Table(name="access_token")
 * @ORM\HasLifecycleCallbacks
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
class AccessToken extends BaseAccessToken
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

    /**
     * @ORM\Column(name="id_token", type="text", nullable=true)
     * @var string
     */
    protected $idToken;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @JMS\Since("1.14")
     * @var \DateTime
     */
    protected $createdAt;

    public function setExpired()
    {
        $now = new \DateTime();
        $this->setExpiresAt($now->getTimestamp());
    }

    public function getIdToken()
    {
        return $this->idToken;
    }

    public function setIdToken($idToken)
    {
        $this->idToken = $idToken;

        return $this;
    }

    public function getUserId($pairwiseSubjectIdSalt)
    {
        $id = $this->getUser()->getId();
        $client = $this->getClient();

        if ($client instanceof Client && $client->getMetadata()) {
            $metadata = $client->getMetadata();
            if ($metadata->getSubjectType() === 'pairwise') {
                $sectorIdentifier = $metadata->getSectorIdentifier();
                $salt = $pairwiseSubjectIdSalt;

                return hash('sha256', $sectorIdentifier.$id.$salt);
            }
        }

        return $id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
}
