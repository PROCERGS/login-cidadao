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
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Validator\Constraints\HostBelongsToClaimProvider;

/**
 * RemoteClaim
 * @package LoginCidadao\RemoteClaimsBundle\Entity
 * @HostBelongsToClaimProvider
 * @ORM\Entity(repositoryClass="LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository")
 * @ORM\Table(name="remote_claim")
 */
class RemoteClaim implements RemoteClaimInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var TagUri
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="display_name", type="string", length=255, nullable=false)
     */
    private $displayName;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string[]
     * @ORM\Column(name="provider_recommended_scope", type="json_array", nullable=true)
     */
    private $providerRecommendedScope;

    /**
     * @var string[]
     * @ORM\Column(name="provider_essential_scope", type="json_array", nullable=true)
     */
    private $providerEssentialScope;

    /**
     * @var ClaimProviderInterface
     *
     * @ORM\OneToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     */
    private $provider;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return RemoteClaim
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return TagUri
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param TagUri $name
     * @return RemoteClaim
     */
    public function setName(TagUri $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     * @return RemoteClaim
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return RemoteClaim
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return ClaimProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param ClaimProviderInterface $provider
     * @return RemoteClaim
     */
    public function setProvider(ClaimProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecommendedScope()
    {
        return $this->providerRecommendedScope;
    }

    /**
     * @param string|string[] $recommendedScope
     * @return RemoteClaimInterface
     */
    public function setRecommendedScope($recommendedScope)
    {

        $this->providerRecommendedScope = $recommendedScope;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getEssentialScope()
    {
        return $this->providerEssentialScope;
    }

    /**
     * @param string|string[] $essentialScope
     * @return RemoteClaimInterface
     */
    public function setEssentialScope($essentialScope)
    {
        $this->providerEssentialScope = $essentialScope;

        return $this;
    }
}
