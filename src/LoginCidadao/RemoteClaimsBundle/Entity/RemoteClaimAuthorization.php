<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

/**
 * Class RemoteClaimAuthorization
 * @package LoginCidadao\RemoteClaimsBundle\Entity
 * @ORM\Entity(repositoryClass="LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository")
 * @ORM\Table(name="remote_claim_authorization")
 */
class RemoteClaimAuthorization implements RemoteClaimAuthorizationInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ClaimProviderInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client")
     * @ORM\JoinColumn(name="claim_provider_id", referencedColumnName="id")
     */
    private $claimProvider;

    /**
     * @var ClientInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var PersonInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @var TagUri
     *
     * @ORM\Column(name="claim_name", type="string", length=255, nullable=false)
     */
    private $claimName;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255, nullable=false)
     */
    private $accessToken;

    /**
     * @param ClaimProviderInterface $claimProvider
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimProvider(ClaimProviderInterface $claimProvider)
    {
        $this->claimProvider = $claimProvider;

        return $this;
    }

    /**
     * @param string $accessToken
     * @return RemoteClaimAuthorizationInterface
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return ClaimProviderInterface
     */
    public function getClaimProvider()
    {
        return $this->claimProvider;
    }

    /**
     * Defines the entity that will be given access to the information provided by the Claim Provider
     *
     * @param ClientInterface $client
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the tag this authorization refers to.
     *
     * @param TagUri $claimName
     * @return RemoteClaimAuthorizationInterface
     */
    public function setClaimName(TagUri $claimName)
    {
        $this->claimName = $claimName;

        return $this;
    }

    /**
     * @return TagUri
     */
    public function getClaimName()
    {
        return $this->claimName;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param PersonInterface $person
     * @return RemoteClaimAuthorization
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }
}
