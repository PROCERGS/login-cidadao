<?php

namespace PROCERGS\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_token")
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
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person")
     */
    protected $user;

    /**
     * @ORM\Column(name="id_token", type="text", nullable=true)
     * @var string
     */
    protected $idToken;

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
        $id     = $this->getUser()->getId();
        $client = $this->getClient();

        if ($client instanceof Client && $client->getMetadata()) {
            $metadata = $client->getMetadata();
            if ($metadata->getSubjectType() === 'pairwise') {
                $sectorIdentifier = $metadata->getSectorIdentifier();
                $salt             = $pairwiseSubjectIdSalt;
                return hash('sha256', $sectorIdentifier.$id.$salt);
            }
        }
        return $id;
    }
}
